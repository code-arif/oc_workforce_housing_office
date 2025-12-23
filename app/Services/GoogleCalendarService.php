<?php

namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName(config('app.name'));
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect_uri'));
        $this->client->addScope(Calendar::CALENDAR);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function authenticate($code)
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new Exception('Error fetching access token: ' . $token['error']);
        }

        return $token;
    }

    public function setAccessToken($token)
    {
        try {
            if (!is_array($token)) {
                throw new Exception('Invalid token format');
            }

            if (!isset($token['access_token'])) {
                throw new Exception('Token missing access_token');
            }

            $this->client->setAccessToken($token);

            if ($this->client->isAccessTokenExpired()) {
                $refreshToken = $this->client->getRefreshToken() ?? $token['refresh_token'] ?? null;

                if (!$refreshToken) {
                    throw new Exception('Token expired and no refresh token available');
                }

                $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

                if (isset($newToken['error'])) {
                    throw new Exception('Error refreshing token: ' . $newToken['error']);
                }

                if (!isset($newToken['refresh_token']) && $refreshToken) {
                    $newToken['refresh_token'] = $refreshToken;
                }

                $this->service = new Calendar($this->client);
                return $newToken;
            }

            $this->service = new Calendar($this->client);
            return null;
        } catch (Exception $e) {
            Log::error('Google Set Token Error', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * List all Google Calendars for the authenticated user
     */
    public function listAllCalendars()
    {
        try {
            if (!$this->service) {
                throw new Exception('Calendar service not initialized');
            }

            $calendarList = $this->service->calendarList->listCalendarList();

            Log::info('Fetched Google Calendars', [
                'count' => count($calendarList->getItems())
            ]);

            return $calendarList->getItems();
        } catch (Exception $e) {
            Log::error('Error listing calendars', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * List events for a specific calendar
     */
    public function listEventsForCalendar($calendarId, $startDate = null, $endDate = null, $maxResults = 2500)
    {
        try {
            if (!$this->service) {
                throw new Exception('Calendar service not initialized');
            }

            $optParams = [
                'maxResults' => $maxResults,
                'orderBy' => 'startTime',
                'singleEvents' => true,
                'timeMin' => $startDate
                    ? Carbon::parse($startDate)->toRfc3339String()
                    : Carbon::now()->subYear()->startOfDay()->toRfc3339String(),
                'timeMax' => $endDate
                    ? Carbon::parse($endDate)->toRfc3339String()
                    : Carbon::now()->addMonths(3)->endOfDay()->toRfc3339String(),
            ];

            $results = $this->service->events->listEvents($calendarId, $optParams);

            Log::info('Fetched events for calendar', [
                'calendar_id' => $calendarId,
                'count' => count($results->getItems())
            ]);

            return $results->getItems();
        } catch (Exception $e) {
            Log::error('Error listing events for calendar', [
                'calendar_id' => $calendarId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create event in a specific Google Calendar
     */
    public function createEvent($work, $calendarId = 'primary')
    {
        try {
            if (!$this->service) {
                throw new Exception('Calendar service not initialized');
            }

            // If work has a calendar with google_calendar_id, use that
            if ($work->calendar && $work->calendar->google_calendar_id) {
                $calendarId = $work->calendar->google_calendar_id;
            }

            if ($work->is_all_day) {
                $event = new Event([
                    'summary' => $work->title,
                    'description' => $work->description,
                    'location' => $work->location,
                    'start' => [
                        'date' => Carbon::parse($work->start_datetime)->toDateString(),
                        'timeZone' => config('app.timezone'),
                    ],
                    'end' => [
                        'date' => Carbon::parse($work->end_datetime)->toDateString(),
                        'timeZone' => config('app.timezone'),
                    ],
                ]);
            } else {
                $event = new Event([
                    'summary' => $work->title,
                    'description' => $work->description,
                    'location' => $work->location,
                    'start' => [
                        'dateTime' => Carbon::parse($work->start_datetime)->toRfc3339String(),
                        'timeZone' => config('app.timezone'),
                    ],
                    'end' => [
                        'dateTime' => Carbon::parse($work->end_datetime)->toRfc3339String(),
                        'timeZone' => config('app.timezone'),
                    ],
                ]);
            }

            $createdEvent = $this->service->events->insert($calendarId, $event);

            Log::info('Google Calendar event created', [
                'event_id' => $createdEvent->getId(),
                'work_id' => $work->id,
                'calendar_id' => $calendarId
            ]);

            return $createdEvent->getId();
        } catch (Exception $e) {
            Log::error('Google Calendar Create Error', [
                'error' => $e->getMessage(),
                'work_id' => $work->id
            ]);
            throw $e;
        }
    }

    /**
     * Update event in Google Calendar
     */
    public function updateEvent($work, $calendarId = 'primary')
    {
        try {
            if (!$this->service) {
                throw new Exception('Calendar service not initialized');
            }

            if (!$work->google_event_id) {
                throw new Exception('No Google event ID found');
            }

            if ($work->calendar && $work->calendar->google_calendar_id) {
                $calendarId = $work->calendar->google_calendar_id;
            }

            $event = $this->service->events->get($calendarId, $work->google_event_id);

            $event->setSummary($work->title);
            $event->setDescription($work->description);
            $event->setLocation($work->location);

            if ($work->is_all_day) {
                $event->setStart(new EventDateTime([
                    'date' => Carbon::parse($work->start_datetime)->toDateString(),
                    'timeZone' => config('app.timezone'),
                ]));

                $event->setEnd(new EventDateTime([
                    'date' => Carbon::parse($work->end_datetime)->toDateString(),
                    'timeZone' => config('app.timezone'),
                ]));
            } else {
                $event->setStart(new EventDateTime([
                    'dateTime' => Carbon::parse($work->start_datetime)->toRfc3339String(),
                    'timeZone' => config('app.timezone'),
                ]));

                $event->setEnd(new EventDateTime([
                    'dateTime' => Carbon::parse($work->end_datetime)->toRfc3339String(),
                    'timeZone' => config('app.timezone'),
                ]));
            }

            $updatedEvent = $this->service->events->update($calendarId, $work->google_event_id, $event);

            Log::info('Google Calendar event updated', [
                'event_id' => $updatedEvent->getId(),
                'work_id' => $work->id
            ]);

            return $updatedEvent->getId();
        } catch (Exception $e) {
            Log::error('Google Calendar Update Error', [
                'error' => $e->getMessage(),
                'work_id' => $work->id
            ]);
            throw $e;
        }
    }

    /**
     * Delete event from Google Calendar
     */
    public function deleteEvent($eventId, $calendarId = 'primary')
    {
        try {
            if (!$this->service) {
                throw new Exception('Calendar service not initialized');
            }

            $this->service->events->delete($calendarId, $eventId);

            Log::info('Google Calendar event deleted', [
                'event_id' => $eventId,
                'calendar_id' => $calendarId
            ]);

            return true;
        } catch (\Google_Service_Exception $e) {
            if ($e->getCode() === 404 || $e->getCode() === 410) {
                return true;
            }
            throw $e;
        }
    }

    /**
     * Legacy method for backward compatibility
     */
    public function listEvents($startDate = null, $endDate = null)
    {
        return $this->listEventsForCalendar('primary', $startDate, $endDate);
    }


    /**
     * Create a new Google Calendar
     */
    public function createGoogleCalendar($name, $description = null, $color = null)
    {
        try {
            if (!$this->service) {
                throw new Exception('Calendar service not initialized');
            }

            $calendar = new \Google\Service\Calendar\Calendar();
            $calendar->setSummary($name);

            if ($description) {
                $calendar->setDescription($description);
            }

            $calendar->setTimeZone(config('app.timezone'));

            $createdCalendar = $this->service->calendars->insert($calendar);

            // Set color if provided
            if ($color && $createdCalendar->getId()) {
                $colorId = $this->getGoogleColorId($color);
                if ($colorId) {
                    $calendarListEntry = new \Google\Service\Calendar\CalendarListEntry();
                    $calendarListEntry->setColorId($colorId);

                    $this->service->calendarList->patch(
                        $createdCalendar->getId(),
                        $calendarListEntry
                    );
                }
            }

            Log::info('Google Calendar created', [
                'calendar_id' => $createdCalendar->getId(),
                'name' => $name
            ]);

            return $createdCalendar;
        } catch (Exception $e) {
            Log::error('Google Calendar Create Error', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update Google Calendar
     */
    public function updateGoogleCalendar($calendarId, $name, $description = null, $color = null)
    {
        try {
            if (!$this->service) {
                throw new Exception('Calendar service not initialized');
            }

            $calendar = $this->service->calendars->get($calendarId);
            $calendar->setSummary($name);

            if ($description !== null) {
                $calendar->setDescription($description);
            }

            $updatedCalendar = $this->service->calendars->update($calendarId, $calendar);

            // Update color if provided
            if ($color) {
                $colorId = $this->getGoogleColorId($color);
                if ($colorId) {
                    $calendarListEntry = new \Google\Service\Calendar\CalendarListEntry();
                    $calendarListEntry->setColorId($colorId);

                    $this->service->calendarList->patch($calendarId, $calendarListEntry);
                }
            }

            Log::info('Google Calendar updated', [
                'calendar_id' => $calendarId,
                'name' => $name
            ]);

            return $updatedCalendar;
        } catch (Exception $e) {
            Log::error('Google Calendar Update Error', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete Google Calendar
     */
    public function deleteGoogleCalendar($calendarId)
    {
        try {
            if (!$this->service) {
                throw new Exception('Calendar service not initialized');
            }

            $this->service->calendars->delete($calendarId);

            Log::info('Google Calendar deleted', [
                'calendar_id' => $calendarId
            ]);

            return true;
        } catch (\Google_Service_Exception $e) {
            if ($e->getCode() === 404 || $e->getCode() === 410) {
                return true; // Already deleted
            }
            throw $e;
        }
    }

    /**
     * Convert hex color to Google Calendar color ID
     */
    private function getGoogleColorId($hexColor)
    {
        $colorMap = [
            '#7986cb' => '1',
            '#33b679' => '2',
            '#8e24aa' => '3',
            '#e67c73' => '4',
            '#f6bf26' => '5',
            '#f4511e' => '6',
            '#039be5' => '7',
            '#616161' => '8',
            '#3f51b5' => '9',
            '#0b8043' => '10',
            '#d50000' => '11',
            '#13bfa6' => '7', // Map to closest Google color
        ];

        return $colorMap[$hexColor] ?? '1';
    }
}
