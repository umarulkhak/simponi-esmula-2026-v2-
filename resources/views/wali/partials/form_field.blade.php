@php
    $isRequired = $required ?? false;
    $inputClass = 'form-control rounded-2 ' . ($errors->has($name) ? 'is-invalid' : '') . ' ' . ($class ?? '');
@endphp

<div class="mb-3">
    <label for="{{ $name }}" class="form-label fw-medium">
        <i class="bi {{ $icon ?? 'bi-input' }} me-1"></i> {{ $label }}
        @if($isRequired)<span class="text-danger">*</span>@endif
    </label>

    @if($type === 'select')
        {!! Form::select($name, $options, null, [
            'class' => $inputClass,
            'id' => $name,
            'placeholder' => $placeholder ?? 'Pilih...',
            'required' => $isRequired
        ]) !!}
    @elseif($type === 'date')
        {!! Form::date($name, $value ?? null, [
            'class' => $inputClass,
            'id' => $name,
            'required' => $isRequired
        ]) !!}
    @else
        {!! Form::text($name, null, [
            'class' => $inputClass,
            'id' => $name,
            'placeholder' => $placeholder ?? '',
            'required' => $isRequired
        ]) !!}
    @endif

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
