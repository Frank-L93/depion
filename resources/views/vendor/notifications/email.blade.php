@component('mail::message')
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Whoops!')
@else
# @lang('Hello!')
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{!! $line !!}

@endforeach
{{-- Table or Game --}}
@if($actionUrl == url('/')."/rankings")
<?php $i = 1;?>
@component('mail::table')
| # | naam | score | waarde |
| -- |:----:| -----:| ---:|
@foreach($data as $ranking)
| <?php echo $i; $i++; ?> | {{$ranking->user->name}} | {{$ranking->score}} | {{$ranking->value}}| 
@endforeach
@endcomponent
@elseif($actionUrl == url('/')."/games")
@foreach($data as $game)
@if($game['white'] == "Geen partij voor jou")
@else
Jouw partij is:
@component('mail::table')
|Wit|Zwart|
|:----:|:----:|
| {{$game['white']}} | {{$game['black']}} |
@endcomponent
@endif
@endforeach

@endif
{{-- Action Button --}}
@isset($actionText)
<?php
    switch ($level) {
        case 'success':
        case 'error':
            $color = $level;
            break;
        default:
            $color = 'primary';
    }
?>
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
<br>
{{ config('app.name') }}
@endif

{{-- Subcopy --}}
@isset($actionText)
@slot('subcopy')
@lang(
    "Je ontvangt deze e-mail omdat je de e-mailnotificaties aan hebt staan.".
    'Werkt de button niet? Gebruik dan de volgende url: [:displayableActionUrl](:actionURL)',
    [
        'actionText' => $actionText,
        'actionURL' => $actionUrl,
        'displayableActionUrl' => $displayableActionUrl,
    ]
)
@endslot
@endisset
@endcomponent
