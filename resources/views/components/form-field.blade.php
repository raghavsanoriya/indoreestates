@props(['name','label','value'=>null,'type'=>'text','required'=>false,'id'=>null])
@php($fieldId=$id ?: preg_replace('/[^a-zA-Z0-9_-]/','-',$name))
@php($oldKey=str_replace(['][','[',']'],['.','.',''],$name))
<div class="mb-3"><label class="form-label" for="{{ $fieldId }}">{{ $label }}@if($required)<span class="text-danger ms-1">*</span>@endif</label>
@if($type==='textarea')<textarea id="{{ $fieldId }}" name="{{ $name }}" {{ $attributes->class(['form-control']) }} @required($required)>{{ old($oldKey,$value) }}</textarea>@else<input id="{{ $fieldId }}" type="{{ $type }}" name="{{ $name }}" value="{{ $type==='password'?'':old($oldKey,$value) }}" {{ $attributes->class(['form-control']) }} @required($required)>@endif
@error($oldKey)<p class="text-danger mt-1">{{ $message }}</p>@enderror</div>
