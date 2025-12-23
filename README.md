# Home Green Valley Landscaping — Backend System

Welcome to **Home Green Valley Landscaping**, a powerful and fully automated backend system built with **Laravel**.  
This platform is designed to simplify the management of landscaping operations — from employee coordination to work scheduling and live team tracking — with seamless Google integration.

---

## Key Features

### Employee Management
- Add, edit, and manage employees easily.  
- Each employee has role-based access and activity tracking.
- Maintain complete employment history, contact info, and assigned work logs.

### Team Management
- Create and organize multiple teams dynamically.  
- Assign an **unlimited number of employees** to any team.  
- Promote or reassign **Team Leaders** with just a few clicks.  
- View team performance and productivity insights directly in the dashboard.

### Work Management
- Create, update, and delete work schedules effortlessly.  
- Each work is linked to a team, employees, and calendar events.  
- Works can be color-coded and filtered by team, priority, or date.  
- Work reports and completion tracking integrated into the dashboard.

### Google Calendar Clone (Real-Time Sync)
> One of the most advanced features of this system — a **fully functional clone of Google Calendar**.

- When a work is created in the dashboard, it **automatically syncs** with Google Calendar in real time.  
- When an event is created directly in Google Calendar, it **instantly syncs back** to the dashboard.  
- Supports **bi-directional synchronization**, so both systems stay up-to-date always.  
- Real-world implementation of Google API integration and calendar event management.

### Real-Time Team Location Tracking
- Track the live location of each team member directly on the dashboard using **Google Maps API**.  
- Integrated with **Geolocation services** to ensure accurate movement data.  
- Real-time updates with **WebSocket** and **Laravel Reverb (or Pusher)**.

### Work Tracking with Google Maps Polyline
- View each team's **entire travel path** visualized on the map using **Google Maps Polyline**.  
- Automatically records and displays the team’s route during a work session.  
- Useful for performance analysis, logistics optimization, and transparency.

---

## Tech Stack

| Layer | Technology |
|:------|:------------|
| **Backend** | Laravel 11 (PHP 8+) |
| **Database** | MySQL / MariaDB |
| **Frontend Assets** | Bootstrap CSS, Vite |
| **Realtime Services** | Laravel Reverb / Pusher |
| **APIs** | Google Maps API, Google Calendar API |
| **Authentication** | JWT |

---

## 📂 Project Structure
```bash
app/ → Core logic (Models, Controllers, Services)
routes/ → API and Web routes
database/ → Migrations, Seeders, Factories
resources/ → Views, CSS, JS (Vite + Tailwind)
public/ → Publicly accessible assets
config/ → Configuration files
```

---

## 🏁 Getting Started

1. **Clone the Repository**
   ```bash
   git clone https://github.com/your-username/home-green-valley-backend.git
   cd home-green-valley-backend
   ```
2. **Install Dependencies**
    ```base
    composer install
    npm install && npm run dev
    php artisan install:broadcasting
    ```

3. **Setup Environment**
    ```base
    cp .env.example .env
    php artisan key:generate
    ```

4. **Run Migrations & Seeders**
    ```base
    php artisan migrate --seed
    ```

5. **IStart the Server**
    ```base
    php artisan serve
    ```



