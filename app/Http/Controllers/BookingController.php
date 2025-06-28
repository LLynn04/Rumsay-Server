<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'service']);

        // If user is not admin, only show their bookings
        if ($request->user()->isUser()) {
            $query->where('user_id', $request->user()->id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'message' => 'Bookings retrieved successfully',
            'data' => $bookings
        ]);
    }

    /**
     * Store a newly created booking (Cash payment only).
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i',
            'notes' => 'sometimes|string|max:500',
        ]);

        $service = Service::findOrFail($request->service_id);

        if (!$service->is_active) {
            return response()->json([
                'message' => 'Service is not available for booking.'
            ]);
        }

        // Check if user already has a booking for the same service on the same date/time
        $existingBooking = Booking::where('user_id', $request->user()->id)
            ->where('service_id', $request->service_id)
            ->where('booking_date', $request->booking_date)
            ->where('booking_time', $request->booking_time)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingBooking) {
            return response()->json([
                'message' => 'You already have a booking for this service at the same date and time.'
            ]);
        }

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'service_id' => $request->service_id,
            'booking_date' => $request->booking_date,
            'booking_time' => $request->booking_time,
            'status' => 'pending',
            'payment_status' => 'pending', // Will be paid in cash
            'total_amount' => $service->price,
            'notes' => $request->notes,
        ]);

        $booking->load(['service', 'user']);

        return response()->json([
            'message' => 'Booking created successfully. Payment will be collected in cash.',
            'data' => $booking,
            'payment_info' => [
                'method' => 'cash',
                'amount' => $service->price,
                'currency' => 'USD',
                'note' => 'Payment will be collected in cash during or after service completion.'
            ]
        ]);
    }

    /**
     * Display the specified booking.
     */
    public function show(Request $request, Booking $booking)
    {
        // Users can only view their own bookings
        if ($request->user()->isUser() && $booking->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ]);
        }

        $booking->load(['service', 'user']);

        return response()->json([
            'message' => 'Booking retrieved successfully',
            'data' => $booking
        ]);
    }

    /**
     * Update booking status (Admin only).
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'sometimes|string|max:500',
        ]);

        $booking->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        $booking->load(['service', 'user']);

        return response()->json([
            'message' => 'Booking status updated successfully',
            'data' => $booking
        ]);
    }

    /**
     * Cancel booking (User can cancel their own pending bookings).
     */
    public function cancel(Request $request, Booking $booking)
    {
        // Users can only cancel their own bookings
        if ($request->user()->isUser() && $booking->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ]);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending bookings can be cancelled'
            ]);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'data' => $booking
        ]);
    }

    /**
     * Mark cash payment as received (Admin only).
     */
    public function markPaymentReceived(Request $request, Booking $booking)
    {
        $request->validate([
            'admin_notes' => 'sometimes|string|max:500',
            'received_amount' => 'required|numeric|min:0',
        ]);

        if ($booking->isPaid()) {
            return response()->json([
                'message' => 'Payment already marked as received'
            ]);
        }

        $booking->update([
            'payment_status' => 'paid',
            'admin_notes' => $request->admin_notes ?? 'Cash payment received - Amount: $' . $request->received_amount,
        ]);

        $booking->load(['service', 'user']);

        return response()->json([
            'message' => 'Cash payment marked as received',
            'data' => $booking
        ]);
    }

    /**
     * Get pending payments (Admin only).
     */
    public function pendingPayments(Request $request)
    {
        $bookings = Booking::with(['user', 'service'])
            ->where('payment_status', 'pending')
            ->whereIn('status', ['approved', 'completed'])
            ->orderBy('booking_date', 'asc')
            ->paginate(15);

        return response()->json([
            'message' => 'Pending payments retrieved successfully',
            'data' => $bookings
        ]);
    }

    /**
     * Get payment summary (Admin only).
     */
    public function paymentSummary(Request $request)
    {
        $request->validate([
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
        ]);

        $query = Booking::query();

        if ($request->has('date_from')) {
            $query->whereDate('booking_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('booking_date', '<=', $request->date_to);
        }

        $summary = [
            'total_bookings' => $query->count(),
            'pending_payments' => $query->clone()->where('payment_status', 'pending')->count(),
            'completed_payments' => $query->clone()->where('payment_status', 'paid')->count(),
            'total_pending_amount' => $query->clone()->where('payment_status', 'pending')->sum('total_amount'),
            'total_received_amount' => $query->clone()->where('payment_status', 'paid')->sum('total_amount'),
            'approved_bookings' => $query->clone()->where('status', 'approved')->count(),
            'rejected_bookings' => $query->clone()->where('status', 'rejected')->count(),
        ];

        return response()->json([
            'message' => 'Payment summary retrieved successfully',
            'data' => $summary
        ]);
    }

    /**
     * Complete booking and mark as done (Admin only).
     */
    public function completeBooking(Request $request, Booking $booking)
    {
        $request->validate([
            'admin_notes' => 'sometimes|string|max:500',
        ]);

        if ($booking->status !== 'approved') {
            return response()->json([
                'message' => 'Only approved bookings can be marked as completed'
            ]);
        }

        $booking->update([
            'status' => 'completed',
            'admin_notes' => $request->admin_notes ?? 'Service completed successfully',
        ]);

        $booking->load(['service', 'user']);

        return response()->json([
            'message' => 'Booking marked as completed',
            'data' => $booking
        ]);
    }
}
