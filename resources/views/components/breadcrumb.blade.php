@props(['title'])
<div class="breadcrumb-bar o3-breadcrumb"><div class="container"><h1 class="breadcrumb-title">{{ $title }}</h1><nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li><li class="breadcrumb-item active" aria-current="page">{{ $title }}</li></ol></nav></div></div>
