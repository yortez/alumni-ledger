@extends('layouts.app')

@section('title', 'My dashboard')

@section('content')
<div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:py-14">
    <div class="flex flex-col justify-between gap-6 border-b border-line pb-8 sm:flex-row sm:items-end"><div><p class="eyebrow">Alumni directory</p><h1 class="font-display mt-3 text-4xl sm:text-5xl">Welcome, {{ auth()->user()->name }}.</h1></div><a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-between gap-8 bg-forest px-5 py-3 text-sm font-semibold text-white hover:bg-forest-dark">{{ $profile?->completed_at ? 'Update profile' : 'Complete profile' }} <span aria-hidden="true">&#8599;</span></a></div>
    <div class="mt-8 grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
        <section class="border border-line bg-white p-6 sm:p-8"><div class="flex items-start justify-between gap-4"><div><p class="eyebrow">Your record</p><h2 class="font-display mt-2 text-2xl">{{ auth()->user()->username }}</h2></div><span class="bg-mint px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-forest">Verified graduate</span></div><dl class="mt-8 grid gap-x-8 gap-y-5 border-t border-line pt-6 sm:grid-cols-2"><div><dt class="text-xs uppercase tracking-wider text-muted">Student number</dt><dd class="mt-1 font-medium">{{ auth()->user()->student_number }}</dd></div><div><dt class="text-xs uppercase tracking-wider text-muted">Email</dt><dd class="mt-1 break-all font-medium">{{ auth()->user()->email }}</dd></div></dl></section>
        <section class="border border-line bg-white p-6 sm:p-8"><p class="eyebrow">Profile status</p>@if ($profile?->completed_at)<p class="font-display mt-4 text-3xl">Your profile is complete.</p><p class="mt-3 text-sm leading-6 text-muted">Last updated {{ $profile->updated_at->format('F j, Y') }}.</p><a href="{{ route('profile.edit') }}" class="mt-6 inline-flex items-center gap-3 text-sm font-semibold text-forest">Review your details <span aria-hidden="true">&#8594;</span></a>@else<p class="font-display mt-4 text-3xl">Add the next chapter.</p><p class="mt-3 text-sm leading-6 text-muted">Your graduate record is ready. Add your current details to complete your alumni profile.</p><a href="{{ route('profile.edit') }}" class="mt-6 inline-flex items-center gap-3 text-sm font-semibold text-terracotta">Complete your profile <span aria-hidden="true">&#8594;</span></a>@endif</section>
    </div>
</div>
@endsection
