@if($property->source_key)
<section class="panel imported-project-details">
    <p class="eyebrow green">IMPORTED PROJECT INFORMATION</p>
    <h2>{{ $property->source_details['developer'] ?? 'Developer' }}</h2>
    <dl class="detail-grid">
        @foreach(['project_type'=>'Project type','development'=>'Development','project_area'=>'Published project area / extent','unit_size'=>'Published unit size range','configurations'=>'Unit configurations','project_status'=>'Developer-reported project status','image_caption'=>'Source image label'] as $key=>$label)
            @if(!empty($property->source_details[$key]))<div><dt>{{ $label }}</dt><dd>{{ $property->source_details[$key] }}</dd></div>@endif
        @endforeach
        <div><dt>Imported on</dt><dd>{{ $property->imported_at?->format('d M Y') }}</dd></div>
    </dl>
    <p class="muted small">Project-level information, not a confirmed individual unit offer. Current availability and pricing must be confirmed. Township area and unit size ranges are not the size of an individual listed property. Images belong to the source developer; artist’s impressions are illustrative. No affiliation is implied.</p>
    <a href="{{ $property->source_url }}" target="_blank" rel="noopener noreferrer" class="button outline small">View developer source ↗</a>
</section>
@endif
