@props(['status'])

@php
$styles = match($status) {
    'Pending'     => 'bg-amber-100 text-amber-800 ring-amber-300/60',
    'Approved'    => 'bg-green-100 text-green-800 ring-green-300/60',
    'Rejected'    => 'bg-red-100 text-red-800 ring-red-300/60',
    'Cancelled'   => 'bg-slate-100 text-slate-600 ring-slate-300/60',
    'Completed'   => 'bg-blue-100 text-blue-800 ring-blue-300/60',
    'Available'   => 'bg-green-100 text-green-800 ring-green-300/60',
    'Booked'      => 'bg-blue-100 text-blue-800 ring-blue-300/60',
    'In Transit'  => 'bg-orange-100 text-orange-800 ring-orange-300/60',
    'Maintenance' => 'bg-amber-100 text-amber-800 ring-amber-300/60',
    'Offline'     => 'bg-red-100 text-red-800 ring-red-300/60',
    default       => 'bg-slate-100 text-slate-600 ring-slate-300/60',
};
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 {{ $styles }}">
    {{ $status }}
</span>
