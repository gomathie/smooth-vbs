@extends('layouts.app')

@section('title', 'Booking #' . $booking->id)
@section('page-title', 'Booking #' . $booking->id)

@section('header-actions')
    <a href="{{ route('bookings.index') }}" class="btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
        </svg>
        Back
    </a>
@endsection

@section('content')
<div class="mx-auto max-w-3xl space-y-6">

    {{-- Booking summary --}}
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Booking Summary</h2>
                <p class="mt-0.5 text-xs text-slate-400">Submitted {{ $booking->created_at->diffForHumans() }}</p>
            </div>
            <x-status-badge :status="$booking->status"/>
        </div>
        <div class="card-body">
            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Vehicle</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900">
                        {{ $booking->vehicle->registration_number }}
                        <span class="font-normal text-slate-500">— {{ $booking->vehicle->vehicle_type }}</span>
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Requested by</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $booking->user->name }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Start</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $booking->start_datetime->format('D d M Y, H:i') }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">End</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $booking->end_datetime->format('D d M Y, H:i') }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Purpose</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $booking->purpose }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Destination</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $booking->destination ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Passengers</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $booking->passenger_count }}</dd>
                </div>

                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Duration</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $booking->start_datetime->diffForHumans($booking->end_datetime, true) }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Approval timeline --}}
    <div class="card">
        <div class="card-header">
            <h2 class="text-sm font-semibold text-slate-900">Approval History</h2>
        </div>
        <div class="card-body">
            @if ($booking->approvals->isEmpty())
                <p class="text-sm text-slate-400">No approval actions yet.</p>
            @else
                <ol class="space-y-4">
                    @foreach ($booking->approvals as $approval)
                        <li class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full
                                    {{ $approval->status === 'Approved' ? 'bg-green-100' : ($approval->status === 'Rejected' ? 'bg-red-100' : 'bg-slate-100') }}">
                                    @if ($approval->status === 'Approved')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-green-600">
                                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/>
                                        </svg>
                                    @elseif ($approval->status === 'Rejected')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-red-600">
                                            <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-slate-400">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </div>
                                @if (!$loop->last)
                                    <div class="mt-1 h-full w-px bg-slate-200"></div>
                                @endif
                            </div>
                            <div class="pb-4">
                                <p class="text-sm font-medium text-slate-900">
                                    {{ $approval->status }}
                                    <span class="font-normal text-slate-500">by {{ $approval->approver->name ?? 'System' }}</span>
                                </p>
                                <p class="text-xs text-slate-400">{{ $approval->acted_at->format('d M Y, H:i') }}</p>
                                @if ($approval->comment)
                                    <p class="mt-1.5 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700">{{ $approval->comment }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>

    {{-- Approve / Reject panel (supervisors & admins) --}}
    @if (auth()->user()->canApproveBookings() && $booking->status === \App\Models\Booking::STATUS_PENDING)
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-semibold text-slate-900">Take Action</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('bookings.update', $booking) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="status" class="form-label">Decision <span class="text-red-500">*</span></label>
                        <select id="status" name="status" required class="form-select">
                            <option value="{{ \App\Models\Booking::STATUS_APPROVED }}">Approve</option>
                            <option value="{{ \App\Models\Booking::STATUS_REJECTED }}">Reject</option>
                        </select>
                    </div>

                    <div>
                        <label for="comment" class="form-label">Comment</label>
                        <textarea
                            id="comment"
                            name="comment"
                            rows="3"
                            class="form-input resize-none"
                            placeholder="Optional note for the requester…"
                        >{{ old('comment') }}</textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">Submit Decision</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Cancel booking --}}
    @if (in_array($booking->status, [\App\Models\Booking::STATUS_PENDING, \App\Models\Booking::STATUS_APPROVED]))
        @if (auth()->id() === $booking->user_id || auth()->user()->isAdmin())
            <div class="card border-red-200">
                <div class="card-body flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-900">Cancel this booking</p>
                        <p class="text-xs text-slate-400 mt-0.5">This action cannot be undone.</p>
                    </div>
                    <form method="POST" action="{{ route('bookings.destroy', $booking) }}" data-confirm="Cancel this booking request?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Cancel Booking</button>
                    </form>
                </div>
            </div>
        @endif
    @endif

</div>
@endsection
