<div class="cms-faq-item">
<div class="d-flex gap-2 mb-2"><input class="form-control" name="faq[{{ $fid }}][{{ $index }}][question]" value="{{ $item['question'] ?? '' }}" placeholder="Question" maxlength="200" aria-label="Question {{ $number }}"><button type="button" class="btn btn-sm btn-light" data-faq-remove aria-label="Remove question {{ $number }}"><span class="material-icons-outlined" aria-hidden="true">delete_outline</span></button></div>
<textarea class="form-control" name="faq[{{ $fid }}][{{ $index }}][answer]" rows="2" maxlength="2000" placeholder="Answer" aria-label="Answer {{ $number }}">{{ $item['answer'] ?? '' }}</textarea>
</div>
