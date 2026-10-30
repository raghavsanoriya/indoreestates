@extends('layouts.dashboard')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/cms.css') }}?v=20261030">
@endpush
@push('scripts')
<script src="{{ asset('js/cms-editor.js') }}?v=20261030" defer></script>
@endpush
@section('dashboard-content')
@php($inputId = fn (string $key) => \App\Http\Controllers\WebsiteContentController::inputId($key))
<div class="card workspace-panel mb-4"><div class="card-body">
<p class="workspace-eyebrow mb-1">WEBSITE CONTENT</p>
<h4 class="mb-2">Edit what visitors see on indoreestates.com</h4>
<p class="mb-3">Changes go live as soon as you save. Every item can be undone or reset to its original text.</p>
<nav class="cms-tabs" aria-label="Website content sections">@foreach($sections as $sectionKey=>$definition)<a href="{{ route('website.edit',$sectionKey) }}" @class(['cms-tab','is-active'=>$sectionKey===$section]) @if($sectionKey===$section) aria-current="page" @endif><span class="material-icons-outlined" aria-hidden="true">{{ $definition['icon'] }}</span>{{ $definition['label'] }}</a>@endforeach</nav>
</div></div>
<form method="post" action="{{ route('website.update',$section) }}" enctype="multipart/form-data" class="cms-form">@csrf @method('PUT')
@foreach($groups as $group=>$fields)
<div class="card workspace-panel mb-4"><div class="card-header workspace-panel-heading"><h5 class="mb-0">{{ $group }}</h5></div><div class="card-body">
@foreach($fields as $key=>$field)
@php($fid = $inputId($key))
<div class="cms-field">
<div class="cms-field-head"><label class="form-label mb-0" for="cms-{{ $fid }}">{{ $field['label'] }}@if($field['required'] ?? false)<span class="text-danger ms-1">*</span>@endif</label>@if($cms->isCustom($key))<span class="workspace-count-chip">Edited</span>@endif
<span class="cms-field-actions">@isset($undoable[$key])<button class="btn btn-sm btn-light" type="submit" form="cms-undo-{{ $fid }}">Undo</button>@endisset @if($cms->isCustom($key))<button class="btn btn-sm btn-light" type="submit" form="cms-reset-{{ $fid }}" data-confirm="Reset “{{ $field['label'] }}” to the original content?">Reset to original</button>@endif</span></div>
@if(!empty($field['help']))<p class="form-text mt-1 mb-2">{{ $field['help'] }}</p>@endif
@switch($field['type'])
@case('textarea')<textarea id="cms-{{ $fid }}" name="content[{{ $fid }}]" class="form-control" rows="3" maxlength="{{ $field['max'] ?? 1000 }}" @required($field['required'] ?? false)>{{ old("content.$fid", $cms->text($key)) }}</textarea>@break
@case('markdown')<div class="cms-md-toolbar btn-group btn-group-sm" role="toolbar" aria-label="Formatting for {{ $field['label'] }}" data-md-target="cms-{{ $fid }}">@foreach(['bold'=>['format_bold','Bold'],'italic'=>['format_italic','Italic'],'h2'=>['title','Heading'],'h3'=>['text_fields','Subheading'],'ul'=>['format_list_bulleted','Bullet list'],'ol'=>['format_list_numbered','Numbered list'],'link'=>['link','Link']] as $action=>[$icon,$label])<button type="button" class="btn btn-light" data-md="{{ $action }}" title="{{ $label }}" aria-label="{{ $label }}"><span class="material-icons-outlined" aria-hidden="true">{{ $icon }}</span></button>@endforeach</div>
<textarea id="cms-{{ $fid }}" name="content[{{ $fid }}]" class="form-control cms-markdown" rows="12" maxlength="{{ $field['max'] ?? 20000 }}" @required($field['required'] ?? false)>{{ old("content.$fid", $cms->text($key)) }}</textarea>
<p class="form-text mb-0">Use the buttons above, or type: <code>**bold**</code>, <code>## Heading</code>, <code>- list item</code>, <code>[link text](https://…)</code>. Leave a blank line between paragraphs.</p>@break
@case('image')<div class="cms-image"><img class="cms-image-preview" src="{{ $cms->image($key) }}" alt="Current {{ strtolower($field['label']) }}" data-preview-for="cms-{{ $fid }}"><div class="flex-grow-1"><input id="cms-{{ $fid }}" type="file" name="images[{{ $fid }}]" class="form-control" accept="image/jpeg,image/png,image/webp"><p class="form-text mb-0">JPG, PNG or WebP, up to 5 MB. Choose a file only to replace the current image.</p></div></div>@break
@case('faq')@php($items = old("faq.$fid", $cms->value($key)))
<div class="cms-faq" data-cms-faq data-max="{{ $field['max'] ?? 20 }}"><div class="cms-faq-list">@foreach($items as $index=>$item)@include('dashboard.partials.cms-faq-item',['fid'=>$fid,'index'=>$index,'item'=>$item,'number'=>$loop->iteration])@endforeach</div>
<template data-faq-template>@include('dashboard.partials.cms-faq-item',['fid'=>$fid,'index'=>'__INDEX__','item'=>['question'=>'','answer'=>''],'number'=>''])</template>
<button type="button" class="btn btn-sm btn-outline-primary" data-faq-add><span class="material-icons-outlined" aria-hidden="true">add</span>Add question</button></div>@break
@default<input id="cms-{{ $fid }}" type="{{ ['url'=>'url','email'=>'email','tel'=>'tel'][$field['type']] ?? 'text' }}" name="content[{{ $fid }}]" value="{{ old("content.$fid", $cms->text($key)) }}" class="form-control" maxlength="{{ ['url'=>500,'email'=>255,'tel'=>30][$field['type']] ?? ($field['max'] ?? 200) }}" @required($field['required'] ?? false)>
@endswitch
@foreach(["content.$fid","images.$fid","faq.$fid"] as $errorKey)@error($errorKey)<p class="text-danger mt-1 mb-0">{{ $message }}</p>@enderror @endforeach
@if($field['type']==='faq')@foreach($errors->get("faq.$fid.*") as $messages)<p class="text-danger mt-1 mb-0">{{ $messages[0] }}</p>@break @endforeach @endif
</div>
@endforeach
</div></div>
@endforeach
<div class="cms-savebar"><button class="btn btn-primary">Save changes</button><a class="btn btn-light" href="{{ route('home') }}" target="_blank" rel="noopener">View website <span aria-hidden="true">↗</span></a></div>
</form>
@foreach($groups as $fields)@foreach($fields as $key=>$field)
@if($cms->isCustom($key))<form id="cms-reset-{{ $inputId($key) }}" method="post" action="{{ route('website.reset',$key) }}" hidden>@csrf @method('DELETE')</form>@endif
@isset($undoable[$key])<form id="cms-undo-{{ $inputId($key) }}" method="post" action="{{ route('website.undo',$key) }}" hidden>@csrf</form>@endisset
@endforeach @endforeach
@endsection
