

@if(session('success'))
    <div class="alert alert-success">
        {{session('success')}}
    </div>
@endif

@if(session()->has('errors') && $errors->first('activation_response'))
    <div class="alert alert-danger" role="alert">
        {!!$errors->first('activation_response')!!}
    </div>
@elseif(session('error'))
    <div class="alert alert-danger">
        {{session('error')}}
    </div>
@endif
