<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Store Course Request
 *
 * This request validates the data for storing a course.
 *
 * @package App\Http\Requests
 * @author Renatus Bernardo
 */
class StoreCourseRequest extends FormRequest
{
    /**
     * Determine whether the current user is authorized to perform this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = $this->user();
        return $user !== null && $user->role === 'admin';
    }

    /**
     * Define the validation rules applied to the incoming request.
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch') || $this->route('id');

        $rules = [
            'instructor_id' => $isUpdate ? ['nullable', 'exists:users,id'] : ['required', 'exists:users,id'],
            'assistant_instructor_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'visibility' => ['required', Rule::in(['public', 'private', 'draft'])],
            
            'public_course' => ['nullable', 'in:0,1,true,false,on,off'],
            'max_students' => ['nullable', 'integer', 'min:1'],
            'difficulty' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            
            'pricing_model' => ['required', Rule::in(['free', 'paid'])],
            'price_currency' => ['nullable', Rule::in(['TZS', 'TSH', 'USD', 'tzs', 'tsh', 'usd'])],
            'regular_price' => ['nullable', 'required_if:pricing_model,paid', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:regular_price'],
            
            'schedule_enabled' => ['nullable', 'in:0,1,true,false,on,off'],
            'schedule_date' => ['nullable', 'required_if:schedule_enabled,1', 'required_if:schedule_enabled,true', 'required_if:schedule_enabled,on', 'date'],
            
            'category' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:255'],
            'course_category_id' => ['nullable', 'exists:course_categories,id'],
            'course_language_id' => ['nullable', 'exists:course_languages,id'],
            'category_label' => ['nullable', 'string', 'max:255'],
            'language_label' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'objectives' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['draft', 'published', 'archived'])],
            'is_live_course' => ['nullable', 'in:0,1,true,false,on,off'],
            'delivery_mode' => ['nullable', Rule::in(['online'])],
            
            'featured_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'], // 5MB
            'intro_video' => ['nullable', 'file', 'mimes:mp4,avi,mov,webm', 'max:512000'], // 500MB
            
            'topics' => ['nullable', 'array'],
            'topics.*.id' => ['nullable', 'integer'],
            'topics.*.title' => ['required_with:topics', 'string', 'max:255'],
            'topics.*.description' => ['nullable', 'string'],
            'topics.*.is_free' => ['nullable', 'in:0,1,true,false,on,off'],
            'topics.*.price' => ['nullable', 'numeric', 'min:0'],
            'topics.*.order' => ['nullable', 'integer'],
            
            'topics.*.lessons' => ['nullable', 'array'],
            'topics.*.lessons.*.title' => ['required_with:topics.*.lessons', 'string', 'max:255'],
            'topics.*.lessons.*.description' => ['nullable', 'string'],
            'topics.*.lessons.*.lesson_type' => ['nullable', Rule::in(['video', 'text', 'pdf', 'ppt', 'audio', 'document', 'live'])],
            'topics.*.lessons.*.content_text' => ['nullable', 'string'],
            'topics.*.lessons.*.video_type' => ['nullable', Rule::in(['url', 'upload', 'live'])],
            'topics.*.lessons.*.video_url' => ['nullable', 'url'],
            'topics.*.lessons.*.video_file' => ['nullable', 'file', 'mimes:mp4,avi,mov,webm', 'max:512000'],
            'topics.*.lessons.*.video_path' => ['nullable', 'string'],
            'topics.*.lessons.*.attachment_file' => ['nullable', 'file', 'mimes:pdf,ppt,pptx,doc,docx,txt,zip,mp3,wav', 'max:512000'],
            'topics.*.lessons.*.attachment_path' => ['nullable', 'string'],
            'topics.*.lessons.*.id' => ['nullable', 'integer'],
            'topics.*.lessons.*.duration' => ['nullable', 'integer', 'min:0'],
            'topics.*.lessons.*.is_preview' => ['nullable', 'in:0,1,true,false,on,off'],
            'topics.*.lessons.*.is_free' => ['nullable', 'in:0,1,true,false,on,off'],
            'topics.*.lessons.*.price' => ['nullable', 'numeric', 'min:0'],
            'topics.*.lessons.*.order' => ['nullable', 'integer'],
        ];
        
        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'instructor_id.required' => 'Please select an assigned registered instructor for this course.',
            'instructor_id.exists' => 'The selected instructor does not exist.',
            'title.required' => 'Course title is required.',
            'pricing_model.required' => 'Please select a pricing model.',
            'regular_price.required_if' => 'Regular price is required for paid courses.',
            'sale_price.lt' => 'Sale price must be less than regular price.',
            'topics.*.title.required_with' => 'Topic title is required.',
            'topics.*.lessons.*.title.required_with' => 'Lesson title is required.',
        ];
    }
}
