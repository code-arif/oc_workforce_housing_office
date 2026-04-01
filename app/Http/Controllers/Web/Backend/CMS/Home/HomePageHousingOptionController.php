<?php

namespace App\Http\Controllers\Web\Backend\CMS\Home;

use App\Helper\Helper;
use App\Models\CMS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class HomePageHousingOptionController extends Controller
{
    /**
     * Store a new accordion item.
     * POST /admin/cms/home/housing-option/accordion/store
     */
    public function storeAccordion(Request $request)
    {
        try {
            $request->validate([
                'title'                  => 'required|string|max:255',
                'bottom_text'            => 'nullable|string|max:1000',
                'cards'                  => 'nullable|array',
                'cards.*.price'          => 'required_with:cards|numeric|min:0',
                'cards.*.property_type'  => 'required_with:cards|string|max:255',
                'cards.*.image'          => 'required_with:cards|image|mimes:png,jpg,jpeg,webp|max:2048',
            ]);

            $cards = $this->processCards($request, []);

            $maxOrder = CMS::where('page', 'home')
                ->where('section', 'housing-options')
                ->where('name', 'accordion')
                ->max('order') ?? 0;

            CMS::create([
                'page'     => 'home',
                'section'  => 'housing-options',
                'name'     => 'accordion',
                'title'    => $request->title,
                'order'    => $maxOrder + 1,
                'metadata' => [
                    'cards'       => $cards,
                    'bottom_text' => $request->bottom_text ?? '',
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Accordion item added successfully!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Accordion Store Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing accordion item.
     * POST /admin/cms/home/housing-option/accordion/update/{id}
     */
    public function updateAccordion(Request $request, $id)
    {
        try {
            $request->validate([
                'title'                  => 'required|string|max:255',
                'bottom_text'            => 'nullable|string|max:1000',
                'cards'                  => 'nullable|array',
                'cards.*.price'          => 'required_with:cards|numeric|min:0',
                'cards.*.property_type'  => 'required_with:cards|string|max:255',
                'cards.*.image'          => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
                'cards.*.existing_image' => 'nullable|string',
            ]);

            $accordion = CMS::where('page', 'home')
                ->where('section', 'housing-options')
                ->where('name', 'accordion')
                ->findOrFail($id);

            $oldCards = $accordion->metadata['cards'] ?? [];
            $this->cleanupRemovedCardImages($oldCards, $request);

            $cards = $this->processCards($request, $oldCards);

            $accordion->update([
                'title'    => $request->title,
                'metadata' => [
                    'cards'       => $cards,
                    'bottom_text' => $request->bottom_text ?? '',
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Accordion item updated successfully!'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Accordion item not found.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Accordion Update Failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete an accordion item.
     * DELETE /admin/cms/home/housing-option/accordion/{id}
     */
    public function destroyAccordion($id)
    {
        try {
            $accordion = CMS::where('page', 'home')
                ->where('section', 'housing-options')
                ->where('name', 'accordion')
                ->findOrFail($id);

            foreach ($accordion->metadata['cards'] ?? [] as $card) {
                if (!empty($card['image'])) Helper::deleteImage($card['image']);
            }

            $accordion->delete();

            return response()->json(['success' => true, 'message' => 'Accordion item deleted successfully!']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Accordion item not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Accordion Delete Failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update sort order.
     * POST /admin/cms/home/housing-option/accordion/update-order
     */
    public function updateOrder(Request $request)
    {
        try {
            $request->validate([
                'orders'            => 'required|array',
                'orders.*.id'       => 'required|integer',
                'orders.*.position' => 'required|integer|min:1',
            ]);

            foreach ($request->orders as $item) {
                CMS::where('id', $item['id'])
                    ->where('page', 'home')
                    ->where('section', 'housing-options')
                    ->where('name', 'accordion')
                    ->update(['order' => $item['position']]);
            }

            return response()->json(['success' => true, 'message' => 'Order updated successfully!']);
        } catch (\Exception $e) {
            Log::error('Accordion Order Update Failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update order: ' . $e->getMessage()], 500);
        }
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function processCards(Request $request, array $oldCards): array
    {
        $cards = [];
        if (!$request->has('cards') || !is_array($request->cards)) return $cards;

        foreach ($request->cards as $index => $cardData) {
            $imagePath = $cardData['existing_image'] ?? null;

            if ($request->hasFile("cards.{$index}.image")) {
                if ($imagePath) Helper::deleteImage($imagePath);
                $imagePath = Helper::uploadImage(
                    $request->file("cards.{$index}.image"),
                    'cms/home/housing-options/cards'
                );
            }

            $cards[] = [
                'image'         => $imagePath,
                'price'         => $cardData['price'],
                'property_type' => $cardData['property_type'],
            ];
        }

        return $cards;
    }

    private function cleanupRemovedCardImages(array $oldCards, Request $request): void
    {
        $retained = collect($request->cards ?? [])->pluck('existing_image')->filter()->toArray();

        foreach ($oldCards as $oldCard) {
            if (!empty($oldCard['image']) && !in_array($oldCard['image'], $retained)) {
                Helper::deleteImage($oldCard['image']);
            }
        }
    }
}
