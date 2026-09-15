<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = Room::orderBy('name')->paginate(10);

        return view('rooms.index', compact('rooms'));
    }

    public function create(): View
    {
        return view('rooms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $room = Room::create($validated);

        if ((int) $room->quantity > 0) {
            Reservation::create([
                'room_id' => $room->id,
                'transaction_type' => 'checkout',
                'quantity' => $room->quantity,
                'reference_document' => 'Initial availability',
                'user_id' => $request->user()->id,
                'created_at' => now(),
            ]);
        }

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Room added successfully.');
    }

    public function show(Room $room): View
    {
        $room->load([
            'reservations' => fn ($query) => $query
                ->with('user')
                ->latest('created_at')
                ->take(10),
        ]);

        return view('rooms.show', compact('room'));
    }

    public function edit(Room $room): View
    {
        return view('rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate($this->rules($room), $this->messages());

        $oldQuantity = (int) $room->quantity;
        $room->update($validated);
        $newQuantity = (int) $room->quantity;

        if ($newQuantity !== $oldQuantity) {
            Reservation::create([
                'room_id' => $room->id,
                'transaction_type' => $newQuantity > $oldQuantity ? 'checkout' : 'booking',
                'quantity' => abs($newQuantity - $oldQuantity),
                'reference_document' => 'Manual availability adjustment',
                'user_id' => $request->user()->id,
                'created_at' => now(),
            ]);
        }

        return redirect()
            ->route('rooms.show', $room)
            ->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $room->delete();

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Room deleted successfully.');
    }

    private function rules(?Room $room = null): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'sku' => [
                'required',
                'string',
                'max:80',
                Rule::unique('rooms', 'sku')->ignore($room?->id),
            ],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:150'],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Room name is required.',
            'sku.required' => 'Room code is required.',
            'sku.unique' => 'This room code is already being used.',
            'category.required' => 'Please select a room type.',
            'quantity.min' => 'Available rooms cannot be negative.',
            'reorder_level.min' => 'Low-availability alert cannot be negative.',
            'unit_price.min' => 'Nightly rate cannot be negative.',
            'unit_price.numeric' => 'Nightly rate must be a valid number.',
        ];
    }
}
