@props(['type' => 'Vehicle', 'size' => 'md'])

@php
$canonical = match (strtolower(trim($type))) {
    'car', 'sedan', 'saloon', 'hatchback', 'coupe', 'automobile' => 'car',
    'truck', 'lorry', 'tipper', 'tanker', 'dump truck'           => 'truck',
    'bike', 'motorcycle', 'motorbike', 'moto', 'scooter', 'quad' => 'bike',
    'pickup', 'pickup truck', 'pick-up', 'bakkie'                 => 'pickup',
    'van', 'minivan', 'cargo van', 'panel van'                    => 'van',
    'bus', 'coach', 'minibus', 'shuttle', 'microbus'              => 'bus',
    'suv', '4x4', 'jeep', 'offroad', 'crossover', '4wd'          => 'suv',
    'trailer', 'semi', 'semi-trailer', 'flatbed'                  => 'trailer',
    'payloader', 'loader', 'bulldozer', 'excavator', 'grader', 'forklift', 'crane' => 'payloader',
    default => 'vehicle',
};

$bg = match ($canonical) {
    'car'       => 'bg-blue-500',
    'truck'     => 'bg-slate-600',
    'bike'      => 'bg-orange-500',
    'pickup'    => 'bg-emerald-600',
    'van'       => 'bg-teal-500',
    'bus'       => 'bg-indigo-500',
    'suv'       => 'bg-cyan-600',
    'trailer'   => 'bg-violet-600',
    'payloader' => 'bg-amber-500',
    default     => 'bg-slate-500',
};

$wrap = match ($size) {
    'sm'  => 'h-8 w-8 rounded-lg',
    'lg'  => 'h-14 w-14 rounded-2xl',
    default => 'h-10 w-10 rounded-xl',
};

$icon = match ($size) {
    'sm'  => 'h-4 w-4',
    'lg'  => 'h-7 w-7',
    default => 'h-5 w-5',
};
@endphp

<div {{ $attributes->merge(['class' => "flex shrink-0 items-center justify-center $wrap $bg"]) }}>
    <svg class="{{ $icon }} text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        @switch($canonical)

            @case('car')
                {{-- Sedan: low roofline, two wheels --}}
                <path d="M4 12.5 L6.5 7 L17.5 7 L20 12.5 L20 15 L4 15 Z"/>
                <circle cx="7.5" cy="16.5" r="1.5"/>
                <circle cx="16.5" cy="16.5" r="1.5"/>
                @break

            @case('truck')
                {{-- Box truck: large cargo box + cab --}}
                <path d="M2 9 H13 V16 H2 Z"/>
                <path d="M13 11 L17.5 11 L20 14 L20 16 L13 16 Z"/>
                <circle cx="5" cy="17.5" r="1.5"/>
                <circle cx="17" cy="17.5" r="1.5"/>
                @break

            @case('bike')
                {{-- Motorcycle: two big wheels + body --}}
                <circle cx="6" cy="15" r="3.5" fill="none" stroke="white" stroke-width="2"/>
                <circle cx="18" cy="15" r="3.5" fill="none" stroke="white" stroke-width="2"/>
                <path d="M9.5 15 L11 10 L14.5 10 L16 15 Z" stroke="white" stroke-width="1.5" fill="white"/>
                <path d="M11 10 L13 7 L15 8" stroke="white" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                @break

            @case('pickup')
                {{-- Pickup: open flat bed on left, cab on right --}}
                <path d="M1 12 H12 V15 H1 Z"/>
                <path d="M12 9 H18 L21 12 L21 15 L12 15 Z"/>
                <circle cx="5" cy="16.5" r="1.5"/>
                <circle cx="16.5" cy="16.5" r="1.5"/>
                @break

            @case('van')
                {{-- Panel van: taller boxy body, cab blended in --}}
                <path d="M2 8 H14 V16 H2 Z"/>
                <path d="M14 10 H18 L21 13 L21 16 L14 16 Z"/>
                <circle cx="5.5" cy="17.5" r="1.5"/>
                <circle cx="17" cy="17.5" r="1.5"/>
                @break

            @case('bus')
                {{-- Bus: long tall rectangular body, multiple windows --}}
                <path d="M2 7 H22 V16 H2 Z"/>
                <rect x="4"  y="9" width="3" height="3" rx="0.5" fill="rgba(255,255,255,0.4)"/>
                <rect x="9"  y="9" width="3" height="3" rx="0.5" fill="rgba(255,255,255,0.4)"/>
                <rect x="14" y="9" width="3" height="3" rx="0.5" fill="rgba(255,255,255,0.4)"/>
                <circle cx="6"  cy="17.5" r="1.5"/>
                <circle cx="18" cy="17.5" r="1.5"/>
                @break

            @case('suv')
                {{-- SUV: taller than car, boxy roofline --}}
                <path d="M3 12 L5.5 6.5 L18.5 6.5 L21 12 L21 15.5 L3 15.5 Z"/>
                <circle cx="7.5" cy="17" r="2"/>
                <circle cx="16.5" cy="17" r="2"/>
                @break

            @case('trailer')
                {{-- Semi-trailer: long flat body + small connector cab --}}
                <path d="M1 10 H18 V15 H1 Z"/>
                <path d="M18 11.5 H21 V15 H18 Z"/>
                <circle cx="4"  cy="16.5" r="1.5"/>
                <circle cx="10" cy="16.5" r="1.5"/>
                <circle cx="20" cy="16.5" r="1.5"/>
                @break

            @case('payloader')
                {{-- Payloader/excavator: large body + bucket arm --}}
                <path d="M2 10 H12 V16 H2 Z"/>
                <path d="M12 11 L17 7 L19 9 L15.5 13 L12 13 Z"/>
                <path d="M15 13 L20 13 L20 16 L15 16 Z"/>
                <circle cx="5"  cy="17.5" r="1.5"/>
                <circle cx="17" cy="17.5" r="1.5"/>
                @break

            @default
                {{-- Generic vehicle --}}
                <path d="M4 12.5 L6.5 7 L17.5 7 L20 12.5 L20 15 L4 15 Z"/>
                <circle cx="7.5" cy="16.5" r="1.5"/>
                <circle cx="16.5" cy="16.5" r="1.5"/>

        @endswitch
    </svg>
</div>
