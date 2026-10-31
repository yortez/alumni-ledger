@php($questionEntries = is_array($questionEntries) ? $questionEntries : [])
@php($nextQuestionIndex = $questionEntries === [] ? 0 : max(array_map('intval', array_keys($questionEntries))) + 1)
<section class="panel overflow-hidden" data-question-builder data-next-index="{{ $nextQuestionIndex }}" data-max-questions="20">
    <header class="flex items-center justify-between gap-3 border-b border-line px-4 py-3">
        <h3 class="text-sm font-semibold">Questions</h3>
        <button type="button" data-add-question class="inline-flex min-h-8 items-center gap-1.5 rounded-md bg-forest px-3 py-1.5 text-[10px] font-semibold text-white transition hover:bg-forest-dark"><span aria-hidden="true">+</span>Add Question</button>
    </header>
    <div data-question-list class="grid gap-2 p-3 sm:p-4">
        @foreach ($questionEntries as $index => $question)
            @php($question = is_array($question) ? $question : ['prompt' => $question])
            @php($questionType = $question['type'] ?? 'long_text')
            @php($optionsText = $question['options_text'] ?? implode("\n", $question['options'] ?? []))
            <article class="rounded-chip border border-hairline bg-surface-raised p-3 sm:p-4" data-question-card>
                <div class="flex items-center gap-2">
                    <span class="grid size-5 shrink-0 place-items-center rounded-full bg-forest text-[10px] font-semibold text-white" data-question-number>{{ $loop->iteration }}</span>
                    <div class="field min-w-0 flex-1"><label for="question-prompt-{{ $index }}" class="sr-only">Question prompt</label><input id="question-prompt-{{ $index }}" name="questions[{{ $index }}][prompt]" value="{{ old("questions.{$index}.prompt", $question['prompt'] ?? '') }}" maxlength="1000" placeholder="Enter your question here..." required data-question-field>@error("questions.{$index}.prompt")<p class="field-error">{{ $message }}</p>@enderror</div>
                    <button type="button" data-remove-question title="Remove question" aria-label="Remove question" class="grid size-7 shrink-0 place-items-center rounded-md bg-danger text-white transition hover:bg-danger/80"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v6m6-6v6" /></svg></button>
                </div>
                <div class="mt-3 grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                    <div class="field"><label for="question-section-{{ $index }}">Section label</label><input id="question-section-{{ $index }}" name="questions[{{ $index }}][section_label]" value="{{ old("questions.{$index}.section_label", $question['section_label'] ?? '') }}" maxlength="120" placeholder="e.g. Employment Status" data-question-field>@error("questions.{$index}.section_label")<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="field"><label for="question-type-{{ $index }}">Question type</label><select id="question-type-{{ $index }}" name="questions[{{ $index }}][type]" data-question-type data-question-field>@foreach (\App\Models\Survey::QUESTION_TYPES as $value => $label)<option value="{{ $value }}" @selected(old("questions.{$index}.type", $questionType) === $value)>{{ $label }}</option>@endforeach</select>@error("questions.{$index}.type")<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div class="flex items-center gap-2 pb-2"><input type="hidden" name="questions[{{ $index }}][required]" value="0" data-question-field><label class="inline-flex items-center gap-1.5 text-[10px] font-semibold uppercase text-muted"><input type="checkbox" name="questions[{{ $index }}][required]" value="1" @checked(old("questions.{$index}.required", $question['required'] ?? true)) data-question-field>Required</label></div>
                </div>
                <div class="mt-3 {{ in_array($questionType, \App\Models\Survey::CHOICE_QUESTION_TYPES, true) ? '' : 'hidden' }}" data-question-options>
                    <div class="field"><label for="question-options-{{ $index }}">Options (one per line)</label><textarea id="question-options-{{ $index }}" name="questions[{{ $index }}][options_text]" rows="3" maxlength="5000" placeholder="Option 1&#10;Option 2&#10;Option 3" data-question-field>{{ old("questions.{$index}.options_text", $optionsText) }}</textarea>@error("questions.{$index}.options")<p class="field-error">{{ $message }}</p>@enderror @error("questions.{$index}.options.*")<p class="field-error">{{ $message }}</p>@enderror</div>
                </div>
                @error("questions.{$index}.required")<p class="field-error mt-2">{{ $message }}</p>@enderror
            </article>
        @endforeach
    </div>
    <p class="px-4 pb-3 text-[10px] text-muted">Add up to 20 questions. Choice questions need at least two options.</p>
    <template data-question-template>
        <article class="rounded-chip border border-hairline bg-surface-raised p-3 sm:p-4" data-question-card>
            <div class="flex items-center gap-2">
                <span class="grid size-5 shrink-0 place-items-center rounded-full bg-forest text-[10px] font-semibold text-white" data-question-number></span>
                <div class="field min-w-0 flex-1"><label for="question-prompt-__INDEX__" class="sr-only">Question prompt</label><input id="question-prompt-__INDEX__" name="questions[__INDEX__][prompt]" maxlength="1000" placeholder="Enter your question here..." required data-question-field></div>
                <button type="button" data-remove-question title="Remove question" aria-label="Remove question" class="grid size-7 shrink-0 place-items-center rounded-md bg-danger text-white transition hover:bg-danger/80"><svg aria-hidden="true" class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v6m6-6v6" /></svg></button>
            </div>
            <div class="mt-3 grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                <div class="field"><label for="question-section-__INDEX__">Section label</label><input id="question-section-__INDEX__" name="questions[__INDEX__][section_label]" maxlength="120" placeholder="e.g. Employment Status" data-question-field></div>
                <div class="field"><label for="question-type-__INDEX__">Question type</label><select id="question-type-__INDEX__" name="questions[__INDEX__][type]" data-question-type data-question-field>@foreach (\App\Models\Survey::QUESTION_TYPES as $value => $label)<option value="{{ $value }}" @selected($value === 'short_text')>{{ $label }}</option>@endforeach</select></div>
                <div class="flex items-center gap-2 pb-2"><input type="hidden" name="questions[__INDEX__][required]" value="0" data-question-field><label class="inline-flex items-center gap-1.5 text-[10px] font-semibold uppercase text-muted"><input type="checkbox" name="questions[__INDEX__][required]" value="1" checked data-question-field>Required</label></div>
            </div>
            <div class="mt-3 hidden" data-question-options><div class="field"><label for="question-options-__INDEX__">Options (one per line)</label><textarea id="question-options-__INDEX__" name="questions[__INDEX__][options_text]" rows="3" maxlength="5000" placeholder="Option 1&#10;Option 2&#10;Option 3" data-question-field></textarea></div></div>
        </article>
    </template>
</section>
