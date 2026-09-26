@props([
    'title' => null,
    'description' => null,
])

@php
    $pageTitle = $title ? $title.' — '.config('twende.name') : config('twende.name');
    $pageDescription = $description ?: __('ui.seo.default_description');
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDescription }}">
<link rel="canonical" href="{{ url()->current() }}">
<meta property="og:site_name" content="{{ config('twende.name') }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ asset(config('twende.logo')) }}">
<meta property="og:type" content="website">
<meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="{{ config('twende.colors.red') }}">
<link rel="icon" href="{{ asset(config('twende.logo')) }}" type="image/png">
<link rel="apple-touch-icon" href="{{ asset(config('twende.logo')) }}">
<link rel="manifest" href="{{ route('manifest') }}">
