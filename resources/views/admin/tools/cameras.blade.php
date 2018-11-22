<div class="btn-group" data-toggle="buttons">
    @foreach($options as $option => $label)
    <label class="btn btn-default btn-sm {{ \Request::get('cameras', '-1') == $option ? 'active' : '' }}">
        <input type="radio" class="cameras" value="{{ $option }}">{{$label}}
    </label>
    @endforeach
</div>