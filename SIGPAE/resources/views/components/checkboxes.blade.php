<div class="checkbox-group">
    @foreach ($items as $item)
        <label class="checkbox-item">
            <input type="checkbox"
                   name="{{ $name }}[]"
                   value="{{ $item }}"
                   {{ in_array($item, old($name, [])) ? 'checked' : '' }}>
            {{ $item }}
        </label>
    @endforeach
</div>
