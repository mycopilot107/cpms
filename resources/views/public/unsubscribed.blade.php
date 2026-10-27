@extends('layouts.public')

@section('title', 'Unsubscribed')
@section('robots', 'noindex, nofollow')

@section('content')
    @include('partials.public-hero', [
        'kicker' => 'Email preferences',
        'title'  => 'You are unsubscribed',
        'lead'   => $email.' will not receive any more emails from Best CPMS.',
    ])
@endsection
