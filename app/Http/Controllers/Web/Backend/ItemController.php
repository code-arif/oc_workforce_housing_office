<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Show Item list (Admin Panel)
     */
    public function index()
    {
        $items = Item::latest()->get();
        return view('backend.items.index', compact('items'));
    }

    /**
     * Store new Item
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|string',
        ]);

        Item::create([
            'name'   => $request->name,
            'price'  => $request->price,
            'status' => 1, // default Active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item Added Successfully',
        ]);
    }

    /**
     * Update existing Item
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|string',
        ]);

        $item = Item::findOrFail($id);
        $item->update([
            'name'  => $request->name,
            'price' => $request->price,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item Updated Successfully',
        ]);
    }

    /**
     * Delete Item
     */
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item Deleted Successfully',
        ]);
    }

    /**
     * Toggle Active / Inactive (Admin only)
     */
    public function status($id)
    {
        $item = Item::findOrFail($id);
        $item->status = !$item->status;
        $item->save();

        return response()->json([
            'success' => true,
            'status'  => $item->status ? 'Active' : 'Inactive',
        ]);
    }

    /**
 * Show only active items
 */
public function activeItems()
    {
        $items = Item::where('status', 1)->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }
}
