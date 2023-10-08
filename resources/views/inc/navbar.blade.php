<div class="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 mb-3 bg-white border-bottom shadow-sm">
    <h5 class="my-0 mr-md-auto font-weight-normal">{{config('app.name', 'KeizerPHP')}}</h5>
    <nav class="my-2 my-md-0 mr-md-3">
      <a class="p-2 text-dark" href="/">Home</a>
      <a class="p-2 text-dark" href="/about">{!! trans('pages.about.header') !!}</a>
    </nav>
    <!-- Authentication Links -->
    @guest
        <a class="btn btn-outline-primary" href="{{ route('login') }}">{{ __('Login') }}</a>
    
    @else
     <div class="dropdown show">
     <a class="btn dropdown-toggle p-2 text-dark" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
            {{ Auth::user()->name }}
    </a>
    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
    <a class="dropdown-item" href="/settings">{!! trans('pages.index.settings') !!}</a>
    <a class="dropdown-item" href="{{ route('logout') }}"
               onclick="event.preventDefault();
                             document.getElementById('logout-form').submit();">
                {{ __('Log Uit') }}
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
    </div>
    </div>
    
           <div id="funky"> <a class="p-2 dropdown-toggle" id="notifications" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" >
                <span class="glyphicon glyphicon-inbox"></span>@if(auth()->user()->unReadNotifications->count() > 0)<span class="badge badge-light">{{auth()->user()->unReadNotifications->count()}}</span>@endif
            </a>
            <ul class="dropdown-menu" aria-labelledby="notificationsMenu" id="notificationsMenu">
            @if(auth()->user()->unReadNotifications->count() > 0)<li class="dropdown-header"><a href="{{route('readNotifications')}}" >Markeer als gelezen</a></li>
            @foreach(auth()->user()->unReadNotifications->take(5) as $notification)
                <li class="dropdown-header"><h5>{{$notification->data['Title']}}</h5><hr>{{$notification->data['Message']}}</li>
                @endforeach
            @else
            <li class="dropdown-header">Je hebt geen meldingen</li>
            @endif
            </ul>
            </div>
             
       
    @endguest
  </div>
  