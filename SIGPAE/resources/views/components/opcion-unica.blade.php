<div class="checkbox-group">
    @foreach ($items as $item)
        <label class="checkbox-item">
            <input type="radio"
                   name="{{ $name }}"
                   value="{{ $item }}"
                   {{ old($name) === $item ? 'checked' : '' }}>
            {{ $item }}
        </label>
    @endforeach
</div>
