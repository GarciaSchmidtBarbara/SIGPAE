<div class="radio-group">
    @foreach ($items as $item)
        <label class="radio-item">
            <input type="radio"
                   name="{{ $name }}"
                   value="{{ $item }}"
                   {{ old($name) === $item ? 'checked' : '' }}>
            <span class="custom-radio"></span>
            {{ $item }}
        </label>
    @endforeach
</div>
