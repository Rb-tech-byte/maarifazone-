
const appBaseUrl = ($('meta[name="app-url"]').attr('content') || window.location.origin).replace(/\/+$/, '');
let currentStep = 1;
let topics = [];
window.quillEditor = null;
let topicSortableInstance = null;
const contentSortableInstances = {};

$(document).ready(function () {
    // Initialize Quill Rich Text Editor first
    if ($('.rich-editor').length) {
        window.quillEditor = new Quill('.rich-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    ['link'],
                    [{ 'align': [] }],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['image']
                ]
            },
            placeholder: 'Describe your course...'
        });

        if (typeof courseData !== 'undefined' && courseData && courseData.description) {
            quillEditor.root.innerHTML = courseData.description;
        }

        $('.ql-toolbar').hide();

        $('.toolbar-btn').on('click', function (e) {
            e.preventDefault();
            const command = $(this).data('command');
            const value = $(this).data('value');

            if (command) {
                quillEditor.focus();
                if (value) {
                    quillEditor.format(command, value);
                } else {
                    quillEditor.format(command, !quillEditor.getFormat()[command]);
                }
            }
        });

        $('.format-select').on('change', function () {
            const format = $(this).val();
            quillEditor.format('header', format === 'Paragraph' ? false : parseInt(format.replace('Heading ', '')));
        });
    }

    const categorySelectEl = document.getElementById('course_category_id');
    const categoryLabelEl = document.getElementById('category_label');
    const languageSelectEl = document.getElementById('course_language_id');
    const languageLabelEl = document.getElementById('language_label');

    const syncTaxonomyLabels = () => {
        if (categorySelectEl && categoryLabelEl) {
            const selected = categorySelectEl.selectedOptions[0];
            if (selected && selected.value) {
                categoryLabelEl.value = selected.text.trim();
            } else if (selected && !selected.value) {
                categoryLabelEl.value = '';
            }
        }

        if (languageSelectEl && languageLabelEl) {
            const selected = languageSelectEl.selectedOptions[0];
            if (selected && selected.value) {
                languageLabelEl.value = selected.text.trim();
            } else if (selected && !selected.value) {
                languageLabelEl.value = '';
            }
        }
    };

    if (categorySelectEl && categoryLabelEl) {
        categorySelectEl.addEventListener('change', syncTaxonomyLabels);
    }

    if (languageSelectEl && languageLabelEl) {
        languageSelectEl.addEventListener('change', syncTaxonomyLabels);
    }

    syncTaxonomyLabels();

    // Initialize course data if editing
    if (typeof courseData !== 'undefined' && courseData) {

        if (categorySelectEl && courseData.course_category_id) {
            categorySelectEl.value = courseData.course_category_id;
        }

        if (languageSelectEl && courseData.course_language_id) {
            languageSelectEl.value = courseData.course_language_id;
        }

        topics = (courseData.topics || []).map(function (topic) {
            return {
                id: topic.id || Date.now() + Math.random(),
                title: topic.title || '',
                description: topic.description || '',
                is_free: Boolean(topic.is_free),
                price: parseFloat(topic.price || 0) || 0,
                lessons: (topic.lessons || []).map(function (lesson) {
                    const lType = lesson.lessonType || lesson.contentType || 'video';
                    return {
                        id: lesson.id || Date.now() + Math.random(),
                        title: lesson.title || '',
                        type: 'lesson',
                        lessonType: lType,
                        contentType: lType,
                        description: lesson.description || '',
                        contentText: lesson.contentText || '',
                        videoType: lesson.videoType || 'url',
                        videoUrl: lesson.videoUrl || '',
                        videoFile: null,
                        videoPath: lesson.videoPath || null,
                        attachmentPath: lesson.attachmentPath || null,
                        attachmentName: lesson.attachmentName || '',
                        attachmentType: lesson.attachmentType || '',
                        materialFile: null,
                        duration: lesson.duration || '',
                        isPreview: Boolean(lesson.isPreview || lesson.isFree),
                        isFree: Boolean(lesson.isFree || lesson.isPreview),
                        price: parseFloat(lesson.price || 0) || 0,
                        isLive: Boolean(lesson.isLive)
                    };
                }),
                quizzes: [],
                assignments: [],
                items: []
            };
        });

        if (courseData.featured_image) {
            $('#featuredImagePreview').html(`<img src="${courseData.featured_image}" alt="Featured Image">`);
        }

        if (courseData.intro_video) {
            $('#introVideoPreview').html(`
                <i class="fa-solid fa-play-circle"></i>
                <span>Current video</span>
                <small>Click to change</small>
            `);
        }

        if (topics.length > 0) {
            topics.forEach(initialiseTopicItemsFromChildren);
            renderTopics();
        }
    }

    function toggleMaxStudentField() {
        const isChecked = $('#public_course').prop('checked');
        const maxStudentsField = $('#max_students');

        if (isChecked) {
            maxStudentsField.prop('disabled', true);
            maxStudentsField.css('opacity', '0.6');
            maxStudentsField.css('cursor', 'not-allowed');
        } else {
            maxStudentsField.prop('disabled', false);
            maxStudentsField.css('opacity', '1');
            maxStudentsField.css('cursor', 'text');
        }
    }

    toggleMaxStudentField();

    $('#public_course').on('change', toggleMaxStudentField);

    $('#schedule_enabled').on('change', function () {
        const isChecked = $(this).prop('checked');
        const datePicker = $('#schedule-date-picker');

        if (isChecked) {
            datePicker.slideDown(300);
        } else {
            datePicker.slideUp(300);
            $('#schedule_date').val('');
        }
    });

    $('.progress-step').on('click', function () {
        const step = $(this).data('step');
        changeStep(step);
    });

    $('.tab-btn').on('click', function () {
        const tab = $(this).data('tab');
        if (tab) {
            $('.tab-btn').removeClass('active');
            $(this).addClass('active');
            $('.tab-pane').removeClass('active');
            $(`#${tab}-tab`).addClass('active');
        }
    });

    $('input[name="pricing_model"]').on('change', function () {
        if ($(this).val() === 'paid') {
            $('#pricingFields').slideDown();
        } else {
            $('#pricingFields').slideUp();
        }
    });

    const $pricingFields = $('#pricingFields');
    if ($pricingFields.length) {
        const exchangeRate = parseFloat($pricingFields.data('exchange-rate')) || 2500;
        let lastSelectedCurrency = $('input[name="price_currency"]:checked').val() || 'TZS';

        const formatMoneyPreview = (amount, code) => {
            const num = parseFloat(amount);
            if (isNaN(num) || num <= 0) return null;
            const formatted = num.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            return code === 'USD' ? `$${formatted}` : `TZS ${formatted}`;
        };

        const refreshPriceConversionPreview = () => {
            const currentCode = $('input[name="price_currency"]:checked').val() || 'TZS';
            const targetCode = currentCode === 'USD' ? 'TZS' : 'USD';
            const symbol = currentCode === 'USD' ? '$' : 'TZS';
            $('.maarifazone-price-currency-symbol').text(symbol);

            const regVal = parseFloat($('#regularPriceInput').val());
            const saleVal = parseFloat($('#salePriceInput').val());
            const convert = (val) => currentCode === 'USD' ? (val * exchangeRate) : (val / exchangeRate);

            const parts = [];
            if (!isNaN(regVal) && regVal > 0) {
                parts.push(`Regular: <strong>${formatMoneyPreview(convert(regVal), targetCode)}</strong>`);
            }
            if (!isNaN(saleVal) && saleVal > 0) {
                parts.push(`Sale: <strong>${formatMoneyPreview(convert(saleVal), targetCode)}</strong>`);
            }

            const label = targetCode === 'USD' ? 'USD ($)' : 'TSH (TZS)';
            $('#maarifazone-price-conversion-preview').html(
                parts.length
                    ? `<i class="fa-solid fa-right-left mr-1"></i> Equivalent in ${label}: ${parts.join(' &middot; ')}`
                    : ''
            );
        };

        $('input[name="price_currency"]').on('change', function () {
            const nextCode = $(this).val();
            if (nextCode && nextCode !== lastSelectedCurrency) {
                const convertInput = ($input) => {
                    const val = parseFloat($input.val());
                    if (!isNaN(val) && val > 0) {
                        const converted = nextCode === 'TZS' ? (val * exchangeRate) : (val / exchangeRate);
                        $input.val(converted.toFixed(2));
                    }
                };
                convertInput($('#regularPriceInput'));
                convertInput($('#salePriceInput'));
                lastSelectedCurrency = nextCode;
            }
            refreshPriceConversionPreview();
        });

        $('#regularPriceInput, #salePriceInput').on('input', refreshPriceConversionPreview);
        refreshPriceConversionPreview();
    }

    $('#featuredImage').on('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $('#featuredImagePreview').html(`<img src="${e.target.result}" alt="Featured Image">`);
            };
            reader.readAsDataURL(file);
        }
    });
    $('#featuredImagePreview').on('click', function () {
        $('#featuredImage').click();
    });

    $('#introVideo').on('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            $('#introVideoPreview').html(`
                <i class="fa-solid fa-play-circle"></i>
                <span>${file.name}</span>
                <small>00:15 min</small>
            `);
        }
    });
    $('#introVideoPreview').on('click', function () {
        $('#introVideo').click();
    });

    $('#courseBuilderForm').on('submit', function (e) {
        e.preventDefault();
        submitForm();
    });


});

function changeStep(step) {
    currentStep = step;
    $('.builder-step').removeClass('active');
    $(`.builder-step[data-step="${step}"]`).addClass('active');
    $('.progress-step').removeClass('active');
    $(`.progress-step[data-step="${step}"]`).addClass('active');

    if (step === 1) {
        $('.btn-prev').hide();
    } else {
        $('.btn-prev').show();
    }

    if (step === 3) {
        $('.btn-next').html('<i class="fa-solid fa-save"></i> Save as Draft');
        $('.btn-next').attr('onclick', 'submitForm(false)');
        $('#publishBtn').show();
    } else {
        $('.btn-next').html('Next <i class="fa-solid fa-chevron-right"></i>');
        $('.btn-next').attr('onclick', 'nextStep()');
        $('#publishBtn').hide();
    }
}

function nextStep() {
    if (currentStep < 3) {
        changeStep(currentStep + 1);
    }
}

function previousStep() {
    if (currentStep > 1) {
        changeStep(currentStep - 1);
    }
}

$(document).on('click', '#publishBtn', function () {
    submitForm(true);
});

function submitForm(publish = false) {
    const form = $('#courseBuilderForm')[0];
    const formData = new FormData();

    const csrfToken = $('input[name="_token"]').val();
    formData.append('_token', csrfToken);

    const methodInput = $('input[name="_method"]');
    if (methodInput.length > 0) {
        formData.append('_method', methodInput.val());
    }

    $(form).find('input[type="text"], input[type="number"], input[type="email"], input[type="url"], input[type="datetime-local"], select, textarea, input[type="radio"]:checked').each(function () {
        const name = $(this).attr('name');
        const value = $(this).val();
        if (name && value !== null && value !== undefined) {
            formData.append(name, value);
        }
    });

    const publicCourseCheckbox = $('#public_course');
    formData.append('public_course', publicCourseCheckbox.is(':checked') ? '1' : '0');

    const scheduleEnabledCheckbox = $('#schedule_enabled');
    formData.append('schedule_enabled', scheduleEnabledCheckbox.is(':checked') ? '1' : '0');

    const isLiveCourseCheckbox = $('#is_live_course');
    if (isLiveCourseCheckbox.length > 0) {
        formData.append('is_live_course', isLiveCourseCheckbox.is(':checked') ? '1' : '0');
    }

    const featuredImageInput = document.getElementById('featuredImage');
    if (featuredImageInput && featuredImageInput.files && featuredImageInput.files.length > 0) {
        formData.append('featured_image', featuredImageInput.files[0]);
    }

    const introVideoInput = document.getElementById('introVideo');
    if (introVideoInput && introVideoInput.files && introVideoInput.files.length > 0) {
        formData.append('intro_video', introVideoInput.files[0]);
    }

    if (publish) {
        formData.append('status', 'published');
        formData.append('publish', '1');
    } else {
        formData.append('status', 'draft');
    }

    if (quillEditor) {
        const content = quillEditor.root.innerHTML;
        $('.rich-editor-textarea').val(content);
        formData.append('description', content);
    }

    const topicsData = JSON.stringify(topics);
    formData.append('topics_json', topicsData);

    topics.forEach((topic, topicIndex) => {
        formData.append(`topics[${topicIndex}][title]`, topic.title || '');
        formData.append(`topics[${topicIndex}][description]`, topic.description || '');
        formData.append(`topics[${topicIndex}][order]`, topicIndex);
        formData.append(`topics[${topicIndex}][is_free]`, topic.is_free ? '1' : '0');
        formData.append(`topics[${topicIndex}][price]`, topic.price || 0);

        if (topic.id) {
            formData.append(`topics[${topicIndex}][id]`, topic.id);
        }

        if (topic.lessons && topic.lessons.length > 0) {
            topic.lessons.forEach((lesson, lessonIndex) => {
                formData.append(`topics[${topicIndex}][lessons][${lessonIndex}][title]`, lesson.title || '');
                formData.append(`topics[${topicIndex}][lessons][${lessonIndex}][description]`, lesson.description || '');
                formData.append(`topics[${topicIndex}][lessons][${lessonIndex}][lesson_type]`, lesson.lessonType || lesson.contentType || 'video');
                formData.append(`topics[${topicIndex}][lessons][${lessonIndex}][content_text]`, lesson.contentText || '');
                formData.append(`topics[${topicIndex}][lessons][${lessonIndex}][video_type]`, lesson.videoType || 'url');
                formData.append(`topics[${topicIndex}][lessons][${lessonIndex}][video_url]`, lesson.videoUrl || '');
                formData.append(`topics[${topicIndex}][lessons][${lessonIndex}][duration]`, lesson.duration || '');
                const isPreview = lesson.isPreview === true || lesson.isPreview === 'true' || lesson.isPreview === 1 || lesson.isPreview === '1';
                const isFree = lesson.isFree === true || lesson.isFree === 'true' || lesson.isFree === 1 || isPreview;
                formData.append(`topics[${topicIndex}][lessons][${lessonIndex}][is_preview]`, isPreview ? '1' : '0');
                formData.append(`topics[${topicIndex}][lessons][${lessonIndex}][is_free]`, isFree ? '1' : '0');
                formData.append(`topics[${topicIndex}][lessons][${lessonIndex}][price]`, lesson.price || 0);
                formData.append(`topics[${topicIndex}][lessons][${lessonIndex}][order]`, lessonIndex);

                if (lesson.id) {
                    formData.append(`topics[${topicIndex}][lessons][${lessonIndex}][id]`, lesson.id);
                }
                if (lesson.videoPath) {
                    formData.append(`topics[${topicIndex}][lessons][${lessonIndex}][video_path]`, lesson.videoPath);
                }
                if (lesson.attachmentPath) {
                    formData.append(`topics[${topicIndex}][lessons][${lessonIndex}][attachment_path]`, lesson.attachmentPath);
                }

                if (lesson.videoType === 'upload' && lesson.videoFile && lesson.videoFile.file) {
                    const videoFile = lesson.videoFile.file;
                    if (videoFile instanceof File) {
                        formData.append(`lesson_video_${topicIndex}_${lessonIndex}`, videoFile);
                        formData.append(`topics[${topicIndex}][lessons][${lessonIndex}][video_file]`, videoFile);
                    }
                }

                const matFile = lesson.materialFile instanceof File ? lesson.materialFile : (lesson.materialFile && lesson.materialFile.file instanceof File ? lesson.materialFile.file : null);
                if (matFile) {
                    formData.append(`lesson_material_${topicIndex}_${lessonIndex}`, matFile);
                    formData.append(`topics[${topicIndex}][lessons][${lessonIndex}][material_file]`, matFile);
                }
            });
        }
    });

    const submitBtn = $('.btn-next');
    const publishBtn = $('#publishBtn');
    const originalNextText = submitBtn.html();
    const originalPublishText = publishBtn.html();
    const isPublishing = publish;

    // Disable both buttons
    submitBtn.prop('disabled', true);
    publishBtn.prop('disabled', true);

    if (isPublishing) {
        publishBtn.html('<i class="fa-solid fa-spinner fa-spin"></i> Publishing...');
    } else {
        submitBtn.html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');
    }

    $.ajax({
        url: form.action,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function (response) {
            const successText = isPublishing ? '<i class="fa-solid fa-clock"></i> Submitted for Review!' : '<i class="fa-solid fa-check"></i> Course Saved!';
            submitBtn.prop('disabled', false).html(successText);
            publishBtn.prop('disabled', false).html(isPublishing ? successText : originalPublishText);
            submitBtn.css('background', isPublishing ? '#f99f1e' : '#10b981');
            if (isPublishing) publishBtn.css('background', '#f99f1e');

            $('#publishBtn').hide();

            $('.course-builder-form .alert-danger').remove();

            const successMessage = isPublishing
                ? 'Course submitted successfully! It is now pending admin approval and will be published once approved.'
                : (response.message || 'Course saved as draft successfully!');

            const successHtml = `
                <div class="alert alert-success" style="background: ${isPublishing ? '#f99f1e' : '#10b981'}; color: white; border: none; border-radius: 8px; padding: 15px 20px; margin-bottom: 20px;">
                    <i class="fa-solid fa-${isPublishing ? 'rocket' : 'check-circle'} me-2"></i>
                    <strong>Success!</strong> ${successMessage}
                </div>
            `;
            $('.course-builder-form').prepend(successHtml);

            $('html, body').animate({ scrollTop: 0 }, 500);

            setTimeout(function () {
                if (response.course && response.course.url) {
                    window.location.href = response.course.url;
                } else {
                    window.location.href = `${appBaseUrl}/instructor/courses`;
                }
            }, 2500);
        },
        error: function (xhr) {
            submitBtn.prop('disabled', false).html(originalNextText);
            publishBtn.prop('disabled', false).html(originalPublishText);

            $('.course-builder-form .alert-success').remove();

            if (xhr.status === 422) {
                const response = xhr.responseJSON;
                let errorHtml = '<div class="alert alert-danger" style="background: #ef4444; color: white; border: none; border-radius: 8px; padding: 15px 20px; margin-bottom: 20px;"><ul class="mb-0" style="padding-left: 20px;">';

                if (response && response.errors) {
                    Object.keys(response.errors).forEach(function (key) {
                        response.errors[key].forEach(function (error) {
                            errorHtml += '<li>' + error + '</li>';
                        });
                    });
                } else if (response && response.message) {
                    errorHtml += '<li>' + response.message + '</li>';
                } else {
                    errorHtml += '<li>Please check the form for errors.</li>';
                }

                errorHtml += '</ul></div>';
                $('.course-builder-form').prepend(errorHtml);
            } else {
                const errorMessage = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'An error occurred while creating the course. Please try again.';

                const errorHtml = `
                    <div class="alert alert-danger" style="background: #ef4444; color: white; border: none; border-radius: 8px; padding: 15px 20px; margin-bottom: 20px;">
                        <i class="fa-solid fa-exclamation-circle me-2"></i>
                        <strong>Error!</strong> ${errorMessage}
                    </div>
                `;
                $('.course-builder-form').prepend(errorHtml);
            }

            $('html, body').animate({ scrollTop: 0 }, 500);
        }
    });
}

function addTopic() {
    const topicId = Date.now();
    const topic = {
        id: topicId,
        title: 'New Topic',
        description: '',
        is_free: false,
        price: '',
        lessons: [],
        quizzes: [],
        assignments: [],
        items: []
    };
    topics.push(topic);
    renderTopics();
}

function renderTopics() {
    const container = $('#curriculumContainer');

    const expandedStates = {};
    topics.forEach(topic => {
        const topicCard = $(`.topic-card[data-topic-id="${topic.id}"]`);
        if (topicCard.length) {
            const topicBody = topicCard.find(`.topic-body[data-topic-body="${topic.id}"]`);
            expandedStates[topic.id] = topicBody.is(':visible');
        }
    });

    container.empty();

    Object.keys(contentSortableInstances).forEach(key => {
        const stillExists = topics.some(topic => String(topic.id) === key);
        if (!stillExists) {
            const instance = contentSortableInstances[key];
            if (instance && typeof instance.destroy === 'function') {
                instance.destroy();
            }
            delete contentSortableInstances[key];
        }
    });

    topics.forEach((topic, index) => {
        if (!Array.isArray(topic.items)) {
            topic.items = [];
        }

        const isFree = topic.is_free === true || topic.is_free === 'true' || topic.is_free === 1 || topic.is_free === '1';

        const topicHtml = `
            <div class="topic-card" data-topic-id="${topic.id}">
                <div class="topic-header">
                    <div class="topic-drag-handle">
                        <i class="fa-solid fa-grip-vertical"></i>
                    </div>
                    <input type="text" class="topic-title-input" value="${topic.title || ''}" placeholder="Topic Title" onchange="updateTopic(${topic.id}, 'title', this.value)">
                    <div class="topic-actions">
                        <button type="button" class="topic-action-btn" onclick="copyTopic(${topic.id})" title="Copy">
                            <i class="fa-solid fa-copy"></i>
                        </button>
                        <button type="button" class="topic-action-btn" onclick="deleteTopic(${topic.id})" title="Delete">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        <button type="button" class="topic-action-btn" onclick="toggleTopic(${topic.id})" title="Collapse">
                            <i class="fa-solid fa-chevron-up"></i>
                        </button>
                    </div>
                </div>
                <div class="topic-body" data-topic-body="${topic.id}" style="display: ${expandedStates[topic.id] === false ? 'none' : 'block'};">
                    <div class="topic-description" style="margin-bottom: 12px;">
                        <textarea class="form-control" rows="2" placeholder="Topic description..." onchange="updateTopic(${topic.id}, 'description', this.value)">${topic.description || ''}</textarea>
                    </div>
                    <div class="topic-pricing-row" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center; padding: 12px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 15px;">
                        <label style="display: inline-flex; align-items: center; gap: 8px; margin: 0; font-weight: 600; font-size: 13px; color: #0f172a; cursor: pointer;">
                            <input type="checkbox" ${isFree ? 'checked' : ''} onchange="updateTopic(${topic.id}, 'is_free', this.checked); renderTopics();" style="width: 17px; height: 17px; accent-color: #10b981; cursor: pointer;">
                            <span><i class="fa-solid fa-gift text-success me-1"></i> Free Topic</span>
                        </label>
                        <div style="display: inline-flex; align-items: center; gap: 8px;">
                            <span style="font-size: 13px; font-weight: 600; color: #475569;">Standalone Price (TZS):</span>
                            <input type="number" min="0" step="1000" class="form-control form-control-sm" style="width: 160px; font-weight: 600;" placeholder="e.g. 50000" value="${topic.price || ''}" ${isFree ? 'disabled' : ''} onchange="updateTopic(${topic.id}, 'price', this.value)">
                        </div>
                        <span style="font-size: 12px; color: #64748b; flex: 1 1 200px;">
                            ${isFree ? '<span class="text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Free for all scholars</span>' : (topic.price && topic.price > 0 ? `<span class="text-primary fw-bold">TZS ${Number(topic.price).toLocaleString()}</span> (Scholars can purchase only this topic)` : 'Included in full course enrollment')}
                        </span>
                    </div>
                    <div class="topic-actions-row" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px;">
                        <button type="button" class="btn-add-content" onclick="addLesson(${topic.id}, 'video')">
                            <i class="fa-solid fa-video text-primary"></i> Video Lecture
                        </button>
                        <button type="button" class="btn-add-content" onclick="addLesson(${topic.id}, 'text')">
                            <i class="fa-solid fa-file-lines text-info"></i> Text / Notes
                        </button>
                        <button type="button" class="btn-add-content" onclick="addLesson(${topic.id}, 'pdf')">
                            <i class="fa-solid fa-file-pdf text-danger"></i> PDF Document
                        </button>
                        <button type="button" class="btn-add-content" onclick="addLesson(${topic.id}, 'ppt')">
                            <i class="fa-solid fa-file-powerpoint text-warning"></i> Presentation (PPT)
                        </button>
                        <button type="button" class="btn-add-content" onclick="addLesson(${topic.id}, 'attachment')">
                            <i class="fa-solid fa-paperclip text-secondary"></i> Learning Material
                        </button>
                        <button type="button" class="btn-add-content" onclick="addLiveLesson(${topic.id})" style="background: rgba(229,62,62,0.1); color:#e53e3e; border-color:#e53e3e;">
                            <i class="fa-solid fa-circle" style="font-size:0.6rem;"></i> Live Session
                        </button>
                    </div>
                    <div class="topic-content-list" data-content-list="${topic.id}">
                        <!-- Content items will be added here -->
                    </div>
                    <div class="topic-footer">
                        <button type="button" class="btn-ok" onclick="saveTopic(${topic.id})">Save Topic</button>
                    </div>
                </div>
            </div>
        `;
        container.append(topicHtml);

        renderTopicContent(topic.id);
    });

    initialiseTopicSortable();
}

function updateTopic(topicId, field, value) {
    const topic = topics.find(t => t.id === topicId);
    if (topic) {
        topic[field] = value;
    }
}

function deleteTopic(topicId) {
    topics = topics.filter(t => t.id !== topicId);
    renderTopics();
}

function copyTopic(topicId) {
    const topic = topics.find(t => t.id === topicId);
    if (topic) {
        const idMap = {
            lesson: {},
            quiz: {},
            assignment: {}
        };

        const cloneCollection = (collection, typeKey) => {
            return (collection || []).map(item => {
                const newId = Date.now() + Math.random();
                idMap[typeKey][item.id] = newId;
                return {
                    ...item,
                    id: newId,
                    type: typeKey
                };
            });
        };

        const newTopic = {
            id: Date.now(),
            title: `${topic.title} (Copy)`,
            description: topic.description || '',
            lessons: cloneCollection(topic.lessons, 'lesson'),
            quizzes: cloneCollection(topic.quizzes, 'quiz'),
            assignments: cloneCollection(topic.assignments, 'assignment'),
            items: []
        };

        newTopic.items = (topic.items || []).map(ref => {
            const mappedId = idMap[ref.type] ? idMap[ref.type][ref.id] : null;
            if (!mappedId) {
                return null;
            }
            return {
                id: mappedId,
                type: ref.type
            };
        }).filter(Boolean);

        initialiseTopicItemsFromChildren(newTopic);
        topics.push(newTopic);
        renderTopics();
    }
}

function toggleTopic(topicId) {
    $(`.topic-body[data-topic-body="${topicId}"]`).slideToggle();
}

function addLesson(topicId, contentType = 'video') {
    const topic = topics.find(t => t.id === topicId);
    if (topic) {
        const lessonId = Date.now();
        const titles = {
            video: 'New Video Lecture',
            text: 'New Text / Article Lesson',
            pdf: 'New PDF Document',
            ppt: 'New Presentation (PPT)',
            attachment: 'New Learning Material / File'
        };
        topic.lessons.push({
            id: lessonId,
            title: titles[contentType] || 'New Lesson',
            type: 'lesson',
            lessonType: contentType,
            contentType: contentType,
            description: '',
            contentText: '',
            videoType: 'url',
            videoUrl: '',
            videoFile: null,
            materialFile: null,
            attachmentName: '',
            attachmentPath: '',
            duration: '',
            isPreview: false,
            isFree: false,
            price: '',
            isLive: false,
        });
        topic.items.push({ id: lessonId, type: 'lesson' });
        renderTopicContent(topicId);
        setTimeout(() => toggleContentEdit(topicId, lessonId, 'lesson'), 100);
    }
}

function addLiveLesson(topicId) {
    const topic = topics.find(t => t.id === topicId);
    if (topic) {
        const lessonId = Date.now();
        topic.lessons.push({
            id: lessonId,
            title: 'New Live Interactive Session',
            type: 'lesson',
            lessonType: 'live',
            contentType: 'live',
            description: '',
            contentText: '',
            videoType: 'live',
            videoUrl: '',
            videoFile: null,
            materialFile: null,
            attachmentName: '',
            attachmentPath: '',
            duration: '',
            isPreview: false,
            isFree: false,
            price: '',
            isLive: true,
        });
        topic.items.push({ id: lessonId, type: 'lesson' });
        renderTopicContent(topicId);
        setTimeout(() => toggleContentEdit(topicId, lessonId, 'lesson'), 100);
    }
}

// Quizzes and assignments are decommissioned in Maarifazone pure digital curriculum
function addQuiz(topicId) {
    addLesson(topicId, 'text');
}

function addAssignment(topicId) {
    addLesson(topicId, 'attachment');
}

function renderTopicContent(topicId) {
    const topic = topics.find(t => t.id === topicId);
    if (!topic) return;

    const container = $(`.topic-content-list[data-content-list="${topicId}"]`);
    container.empty();

    ensureTopicItems(topic);

    topic.items.forEach(itemReference => {
        const item = findTopicItemByReference(topic, itemReference);
        if (!item) {
            return;
        }

        const isExpanded = item.expanded || false;
        const cType = item.lessonType || item.contentType || (item.videoType === 'live' ? 'live' : (item.type === 'lesson' ? 'video' : item.type));

        let iconClass = 'fa-play-circle text-primary';
        let typeBadge = 'Video';
        if (cType === 'text') {
            iconClass = 'fa-file-lines text-info';
            typeBadge = 'Text / Notes';
        } else if (cType === 'pdf') {
            iconClass = 'fa-file-pdf text-danger';
            typeBadge = 'PDF Document';
        } else if (cType === 'ppt') {
            iconClass = 'fa-file-powerpoint text-warning';
            typeBadge = 'Presentation';
        } else if (cType === 'attachment') {
            iconClass = 'fa-paperclip text-secondary';
            typeBadge = 'Material';
        } else if (cType === 'live') {
            iconClass = 'fa-satellite-dish text-danger';
            typeBadge = 'Live Session';
        }

        const isFree = item.isPreview === true || item.isPreview === 'true' || item.isPreview === 1 || item.isPreview === '1' || item.isFree === true || item.isFree === 'true' || item.isFree === 1 || item.isFree === '1';

        let optionsHtml = `
            <div class="content-item-options" style="display: ${isExpanded ? 'block' : 'none'}; padding: 15px; background: #ffffff; border-radius: 8px; margin-top: 10px; border: 1px solid #e2e8f0;">
                <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                    <span style="font-size: 12px; font-weight: 700; color: #64748b; align-self: center; margin-right: 6px;">Format:</span>
                    <button type="button" class="btn btn-sm ${cType === 'video' ? 'btn-primary' : 'btn-light'}" style="font-size: 12px; padding: 4px 10px;" onclick="updateContentItem(${topicId}, ${item.id}, 'lesson', 'lessonType', 'video'); updateContentItem(${topicId}, ${item.id}, 'lesson', 'contentType', 'video'); renderTopicContent(${topicId});">
                        <i class="fa-solid fa-video me-1"></i> Video
                    </button>
                    <button type="button" class="btn btn-sm ${cType === 'text' ? 'btn-info text-white' : 'btn-light'}" style="font-size: 12px; padding: 4px 10px;" onclick="updateContentItem(${topicId}, ${item.id}, 'lesson', 'lessonType', 'text'); updateContentItem(${topicId}, ${item.id}, 'lesson', 'contentType', 'text'); renderTopicContent(${topicId});">
                        <i class="fa-solid fa-file-lines me-1"></i> Text / Notes
                    </button>
                    <button type="button" class="btn btn-sm ${cType === 'pdf' ? 'btn-danger' : 'btn-light'}" style="font-size: 12px; padding: 4px 10px;" onclick="updateContentItem(${topicId}, ${item.id}, 'lesson', 'lessonType', 'pdf'); updateContentItem(${topicId}, ${item.id}, 'lesson', 'contentType', 'pdf'); renderTopicContent(${topicId});">
                        <i class="fa-solid fa-file-pdf me-1"></i> PDF
                    </button>
                    <button type="button" class="btn btn-sm ${cType === 'ppt' ? 'btn-warning text-dark' : 'btn-light'}" style="font-size: 12px; padding: 4px 10px;" onclick="updateContentItem(${topicId}, ${item.id}, 'lesson', 'lessonType', 'ppt'); updateContentItem(${topicId}, ${item.id}, 'lesson', 'contentType', 'ppt'); renderTopicContent(${topicId});">
                        <i class="fa-solid fa-file-powerpoint me-1"></i> PPT
                    </button>
                    <button type="button" class="btn btn-sm ${cType === 'attachment' ? 'btn-secondary text-white' : 'btn-light'}" style="font-size: 12px; padding: 4px 10px;" onclick="updateContentItem(${topicId}, ${item.id}, 'lesson', 'lessonType', 'attachment'); updateContentItem(${topicId}, ${item.id}, 'lesson', 'contentType', 'attachment'); renderTopicContent(${topicId});">
                        <i class="fa-solid fa-paperclip me-1"></i> Material
                    </button>
                    <button type="button" class="btn btn-sm ${cType === 'live' ? 'btn-danger' : 'btn-light'}" style="font-size: 12px; padding: 4px 10px;" onclick="updateContentItem(${topicId}, ${item.id}, 'lesson', 'lessonType', 'live'); updateContentItem(${topicId}, ${item.id}, 'lesson', 'contentType', 'live'); updateContentItem(${topicId}, ${item.id}, 'lesson', 'videoType', 'live'); renderTopicContent(${topicId});">
                        <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i> Live
                    </button>
                </div>

                <div class="form-group mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: #334155;">Lesson / Material Overview</label>
                    <textarea class="form-control" rows="2" placeholder="Brief summary or description..." onchange="updateContentItem(${topicId}, ${item.id}, 'lesson', 'description', this.value)">${item.description || ''}</textarea>
                </div>
        `;

        if (cType === 'text') {
            optionsHtml += `
                <div class="form-group mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: #334155;"><i class="fa-solid fa-file-lines text-info me-1"></i> Text / Article Content (Full Lesson Notes)</label>
                    <textarea class="form-control" rows="8" placeholder="Type or paste lecture notes, concepts, code, or article content here..." onchange="updateContentItem(${topicId}, ${item.id}, 'lesson', 'contentText', this.value)">${item.contentText || ''}</textarea>
                    <small class="text-muted">Scholars can read this content directly inside the Maarifazone learning viewer.</small>
                </div>
                <div class="form-group mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: #334155;">Estimated Reading Time (minutes)</label>
                    <input type="number" class="form-control" placeholder="10" min="0" value="${item.duration || ''}" onchange="updateContentItem(${topicId}, ${item.id}, 'lesson', 'duration', this.value)">
                </div>
            `;
        } else if (cType === 'pdf') {
            const hasFile = item.materialFile || item.attachmentName || item.attachmentPath;
            const fileName = item.materialFile ? item.materialFile.name : (item.attachmentName || (item.attachmentPath ? 'Existing PDF Document' : ''));
            optionsHtml += `
                <div class="form-group mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: #334155;"><i class="fa-solid fa-file-pdf text-danger me-1"></i> PDF Document File</label>
                    <div class="file-upload-area p-3 text-center" style="border: 2px dashed #cbd5e1; border-radius: 8px; background: #f8fafc; cursor: pointer;" onclick="document.getElementById('pdf-upload-${item.id}').click()">
                        <input type="file" id="pdf-upload-${item.id}" accept=".pdf,application/pdf" style="display: none;" onchange="handleLessonMaterial(${topicId}, ${item.id}, this.files, 'pdf')">
                        <i class="fa-solid fa-file-pdf fa-2x text-danger mb-2"></i>
                        <p class="mb-1" style="font-size: 14px; font-weight: 600; color: #334155;">Click to upload PDF document</p>
                        <small class="text-muted">PDF files up to 50MB</small>
                        ${hasFile ? `
                            <div class="mt-2 text-success fw-bold" style="font-size: 13px;">
                                <i class="fa-solid fa-check-circle me-1"></i> ${fileName}
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" onclick="event.stopPropagation(); removeLessonMaterial(${topicId}, ${item.id});"><i class="fa-solid fa-times"></i> Remove</button>
                            </div>
                        ` : ''}
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: #334155;">Estimated Reading Time (minutes)</label>
                    <input type="number" class="form-control" placeholder="15" min="0" value="${item.duration || ''}" onchange="updateContentItem(${topicId}, ${item.id}, 'lesson', 'duration', this.value)">
                </div>
            `;
        } else if (cType === 'ppt') {
            const hasFile = item.materialFile || item.attachmentName || item.attachmentPath;
            const fileName = item.materialFile ? item.materialFile.name : (item.attachmentName || (item.attachmentPath ? 'Existing PPT Presentation' : ''));
            optionsHtml += `
                <div class="form-group mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: #334155;"><i class="fa-solid fa-file-powerpoint text-warning me-1"></i> Presentation Slide Deck (PPT/PPTX)</label>
                    <div class="file-upload-area p-3 text-center" style="border: 2px dashed #cbd5e1; border-radius: 8px; background: #f8fafc; cursor: pointer;" onclick="document.getElementById('ppt-upload-${item.id}').click()">
                        <input type="file" id="ppt-upload-${item.id}" accept=".ppt,.pptx,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation" style="display: none;" onchange="handleLessonMaterial(${topicId}, ${item.id}, this.files, 'ppt')">
                        <i class="fa-solid fa-file-powerpoint fa-2x text-warning mb-2"></i>
                        <p class="mb-1" style="font-size: 14px; font-weight: 600; color: #334155;">Click to upload PowerPoint file</p>
                        <small class="text-muted">PPT, PPTX files up to 50MB</small>
                        ${hasFile ? `
                            <div class="mt-2 text-success fw-bold" style="font-size: 13px;">
                                <i class="fa-solid fa-check-circle me-1"></i> ${fileName}
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" onclick="event.stopPropagation(); removeLessonMaterial(${topicId}, ${item.id});"><i class="fa-solid fa-times"></i> Remove</button>
                            </div>
                        ` : ''}
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: #334155;">Duration / Review Time (minutes)</label>
                    <input type="number" class="form-control" placeholder="20" min="0" value="${item.duration || ''}" onchange="updateContentItem(${topicId}, ${item.id}, 'lesson', 'duration', this.value)">
                </div>
            `;
        } else if (cType === 'attachment') {
            const hasFile = item.materialFile || item.attachmentName || item.attachmentPath;
            const fileName = item.materialFile ? item.materialFile.name : (item.attachmentName || (item.attachmentPath ? 'Existing Learning Material' : ''));
            optionsHtml += `
                <div class="form-group mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: #334155;"><i class="fa-solid fa-paperclip text-secondary me-1"></i> Learning Material / Resource File</label>
                    <div class="file-upload-area p-3 text-center" style="border: 2px dashed #cbd5e1; border-radius: 8px; background: #f8fafc; cursor: pointer;" onclick="document.getElementById('mat-upload-${item.id}').click()">
                        <input type="file" id="mat-upload-${item.id}" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.txt" style="display: none;" onchange="handleLessonMaterial(${topicId}, ${item.id}, this.files, 'attachment')">
                        <i class="fa-solid fa-cloud-arrow-up fa-2x text-primary mb-2"></i>
                        <p class="mb-1" style="font-size: 14px; font-weight: 600; color: #334155;">Click to upload document, spreadsheet, code, or ZIP archive</p>
                        <small class="text-muted">PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, ZIP up to 50MB</small>
                        ${hasFile ? `
                            <div class="mt-2 text-success fw-bold" style="font-size: 13px;">
                                <i class="fa-solid fa-check-circle me-1"></i> ${fileName}
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" onclick="event.stopPropagation(); removeLessonMaterial(${topicId}, ${item.id});"><i class="fa-solid fa-times"></i> Remove</button>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        } else if (cType === 'live') {
            optionsHtml += `
                <div style="padding:14px 16px;background:rgba(229,62,62,0.06);border:1px dashed #e53e3e;border-radius:8px; margin-bottom: 15px;">
                    <p style="margin:0;color:#e53e3e;font-size:13px;font-weight:700;"><i class="fa-solid fa-satellite-dish me-2"></i> Live Interactive Session</p>
                    <p style="margin:4px 0 0;color:#555;font-size:12px;">This is a scheduled live interactive class. Scholars will receive joining links and live broadcast in their portal.</p>
                </div>
                <div class="form-group mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: #334155;">Session Duration (minutes)</label>
                    <input type="number" class="form-control" placeholder="60" min="0" value="${item.duration || ''}" onchange="updateContentItem(${topicId}, ${item.id}, 'lesson', 'duration', this.value)">
                </div>
            `;
        } else {
            // Default: Video
            const videoType = item.videoType || 'url';
            optionsHtml += `
                <div class="form-group mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: #334155;">Video Source</label>
                    <div style="display: flex; gap: 15px; margin-bottom: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px;">
                            <input type="radio" name="videoType-${item.id}" value="url" ${videoType === 'url' ? 'checked' : ''} onchange="updateContentItem(${topicId}, ${item.id}, 'lesson', 'videoType', 'url'); renderTopicContent(${topicId});" style="width: 17px; height: 17px; cursor: pointer; margin: 0;">
                            <span>Video URL (YouTube, Vimeo, MP4)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px;">
                            <input type="radio" name="videoType-${item.id}" value="upload" ${videoType === 'upload' ? 'checked' : ''} onchange="updateContentItem(${topicId}, ${item.id}, 'lesson', 'videoType', 'upload'); renderTopicContent(${topicId});" style="width: 17px; height: 17px; cursor: pointer; margin: 0;">
                            <span>Upload Video File</span>
                        </label>
                    </div>
                    ${videoType === 'url' ? `
                        <input type="url" class="form-control" placeholder="https://www.youtube.com/watch?v=... or direct MP4 link" value="${item.videoUrl || ''}" onchange="updateContentItem(${topicId}, ${item.id}, 'lesson', 'videoUrl', this.value)">
                    ` : `
                        <div class="file-upload-area p-3 text-center" style="border: 2px dashed #cbd5e1; border-radius: 8px; background: #f8fafc; cursor: pointer;" onclick="document.getElementById('lesson-video-${item.id}').click()">
                            <input type="file" class="file-upload-input" id="lesson-video-${item.id}" accept="video/*" style="display: none;" onchange="handleVideoUpload(${topicId}, ${item.id}, this.files)">
                            <i class="fa-solid fa-video fa-2x text-primary mb-2"></i>
                            <p class="mb-1" style="font-size: 14px; font-weight: 600; color: #334155;">Click to upload video file or drag and drop</p>
                            <small class="text-muted">MP4, AVI, MOV, WEBM (Max 500MB)</small>
                            ${item.videoFile ? `<div class="mt-2 text-success fw-bold" style="font-size: 13px;"><i class="fa-solid fa-check-circle me-1"></i> ${item.videoFile.name}</div>` : (item.videoPath ? `<div class="mt-2 text-success fw-bold" style="font-size: 13px;"><i class="fa-solid fa-check-circle me-1"></i> Current video uploaded</div>` : '')}
                        </div>
                    `}
                </div>
                <div class="form-group mb-3">
                    <label style="font-weight: 600; font-size: 13px; color: #334155;">Duration (minutes)</label>
                    <input type="number" class="form-control" placeholder="15" min="0" value="${item.duration || ''}" onchange="updateContentItem(${topicId}, ${item.id}, 'lesson', 'duration', this.value)">
                </div>
            `;
        }

        // Modular Lesson Pricing & Free Preview Row (Applies to all lesson content formats)
        optionsHtml += `
                <div class="lesson-pricing-box" style="margin-top: 15px; padding: 12px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
                        <label style="display: inline-flex; align-items: center; gap: 8px; margin: 0; font-weight: 600; font-size: 13px; color: #0f172a; cursor: pointer;">
                            <input type="checkbox" ${isFree ? 'checked' : ''} onchange="updateContentItem(${topicId}, ${item.id}, 'lesson', 'isPreview', this.checked); updateContentItem(${topicId}, ${item.id}, 'lesson', 'isFree', this.checked); renderTopicContent(${topicId});" style="width: 17px; height: 17px; accent-color: #10b981; cursor: pointer;">
                            <span><i class="fa-solid fa-gift text-success me-1"></i> Free Preview (Scholars can access free)</span>
                        </label>
                        <div style="display: inline-flex; align-items: center; gap: 8px;">
                            <span style="font-size: 13px; font-weight: 600; color: #475569;">Lesson Standalone Price (TZS):</span>
                            <input type="number" min="0" step="500" class="form-control form-control-sm" style="width: 150px; font-weight: 600;" placeholder="Optional" value="${item.price || ''}" ${isFree ? 'disabled' : ''} onchange="updateContentItem(${topicId}, ${item.id}, 'lesson', 'price', this.value)">
                        </div>
                        <small style="color: #64748b; font-size: 11px; flex: 1 1 200px;">
                            ${isFree ? '<span class="text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Free lesson</span>' : (item.price && item.price > 0 ? `<span class="text-primary fw-bold">TZS ${Number(item.price).toLocaleString()}</span> (scholars can purchase this lesson individually)` : 'Included in topic or full course enrollment')}
                        </small>
                    </div>
                </div>
            </div>
        `;

        const itemHtml = `
            <div class="content-item" data-item-id="${item.id}" data-item-type="lesson" style="margin-bottom: 10px; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff;">
                <div class="content-item-header" style="display: flex; align-items: center; padding: 10px 14px; gap: 10px;">
                    <i class="fa-solid ${iconClass}" style="font-size: 16px;"></i>
                    <span class="badge bg-light text-dark border" style="font-size: 11px; font-weight: 600;">${typeBadge}</span>
                    <input type="text" class="content-item-title form-control form-control-sm" style="font-weight: 600; flex: 1;" value="${item.title || ''}" placeholder="Title" onchange="updateContentItem(${topicId}, ${item.id}, 'lesson', 'title', this.value)">
                    ${isFree ? '<span class="badge bg-success" style="font-size: 10px;">FREE</span>' : (item.price && item.price > 0 ? `<span class="badge bg-primary" style="font-size: 10px;">TZS ${Number(item.price).toLocaleString()}</span>` : '')}
                    <div class="content-item-actions" style="display: flex; gap: 6px;">
                        <button type="button" class="btn btn-sm btn-light" onclick="toggleContentEdit(${topicId}, ${item.id}, 'lesson')" title="${isExpanded ? 'Collapse' : 'Expand'}">
                            <i class="fa-solid fa-${isExpanded ? 'chevron-up' : 'chevron-down'}"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-light text-danger" onclick="deleteContentItem(${topicId}, ${item.id}, 'lesson')" title="Delete">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
                ${optionsHtml}
            </div>
        `;
        container.append(itemHtml);
    });

    initialiseContentSortable(topicId);
}

function updateContentItem(topicId, itemId, type, field, value) {
    const topic = topics.find(t => t.id === topicId);
    if (!topic) return;

    let item;
    if (type === 'lesson') {
        item = topic.lessons.find(l => l.id === itemId);
    } else if (type === 'quiz') {
        item = topic.quizzes.find(q => q.id === itemId);
    } else if (type === 'assignment') {
        item = topic.assignments.find(a => a.id === itemId);
    }

    if (item) {
        item[field] = value;
    }
}

function toggleContentEdit(topicId, itemId, type) {
    const topic = topics.find(t => t.id === topicId);
    if (!topic) return;

    let item;
    if (type === 'lesson') {
        item = topic.lessons.find(l => l.id === itemId);
    } else if (type === 'quiz') {
        item = topic.quizzes.find(q => q.id === itemId);
    } else if (type === 'assignment') {
        item = topic.assignments.find(a => a.id === itemId);
    }

    if (item) {
        item.expanded = !item.expanded;
        renderTopicContent(topicId);
    }
}

function deleteContentItem(topicId, itemId, type) {
    const topic = topics.find(t => t.id === topicId);
    if (topic) {
        if (type === 'lesson') {
            topic.lessons = topic.lessons.filter(l => l.id !== itemId);
        } else if (type === 'quiz') {
            topic.quizzes = topic.quizzes.filter(q => q.id !== itemId);
        } else {
            topic.assignments = topic.assignments.filter(a => a.id !== itemId);
        }
        topic.items = (topic.items || []).filter(ref => !(ref.id === itemId && ref.type === type));
        renderTopicContent(topicId);
    }
}

function saveTopic(topicId) {
    const topic = topics.find(t => t.id === topicId);
    if (!topic) return;

    const topicCard = $(`.topic-card[data-topic-id="${topicId}"]`);
    const topicTitleInput = topicCard.find('.topic-title-input');
    const topicTitle = topicTitleInput.val() || topic.title;

    if (!topicTitle || topicTitle.trim() === '') {
        alert('Please enter a topic title');
        topicTitleInput.focus();
        return;
    }

    topic.title = topicTitle.trim();

    topicCard.find(`.topic-body[data-topic-body="${topicId}"]`).slideUp(300);

    topicCard.css('border', '2px solid var(--theme)');
    setTimeout(() => {
        topicCard.css('border', '1px solid rgba(108, 112, 111, 0.1)');
    }, 2000);
}

// Quiz Question Functions
function addQuestion(topicId, quizId) {
    const topic = topics.find(t => t.id === topicId);
    if (!topic) return;

    const quiz = topic.quizzes.find(q => q.id === quizId);
    if (!quiz) return;

    if (!quiz.questions) quiz.questions = [];

    const questionId = Date.now();
    quiz.questions.push({
        id: questionId,
        question: '',
        type: 'multiple-choice',
        options: ['', '', '', ''],
        correctAnswer: 0
    });

    quiz.expanded = true;
    renderTopicContent(topicId);
}

function renderQuestion(question, index, topicId, quizId) {
    return `
        <div class="question-item" style="background: rgba(108, 112, 111, 0.05); padding: 15px; border-radius: 8px; margin-bottom: 10px; border: 1px solid rgba(108, 112, 111, 0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <strong style="color: var(--header);">Question ${index}</strong>
                <button type="button" onclick="deleteQuestion(${topicId}, ${quizId}, ${question.id})" style="background: #ef4444; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
            </div>
            <div class="form-group">
                <label>Question Text</label>
                <input type="text" class="form-control" placeholder="Enter your question..." value="${question.question || ''}" onchange="updateQuestion(${topicId}, ${quizId}, ${question.id}, 'question', this.value)">
            </div>
            <div class="form-group">
                <label>Question Type</label>
                <select class="form-control" onchange="updateQuestionType(${topicId}, ${quizId}, ${question.id}, this.value)">
                    <option value="multiple-choice" ${question.type === 'multiple-choice' ? 'selected' : ''}>Multiple Choice</option>
                    <option value="true-false" ${question.type === 'true-false' ? 'selected' : ''}>True/False</option>
                </select>
            </div>
            ${question.type === 'multiple-choice' || question.type === 'true-false' ? `
                <div class="form-group">
                    <label>Options ${question.type === 'true-false' ? '(Select Correct Answer)' : ''}</label>
                    ${(question.type === 'true-false' ? ['True', 'False'] : (question.options || ['', '', '', ''])).map((opt, optIdx) => {
        if (question.type === 'multiple-choice' && optIdx >= 4) return '';
        return `
                            <div style="display: flex; gap: 10px; margin-bottom: 8px; align-items: center;">
                                <input type="radio" name="correct-${question.id}" value="${optIdx}" ${question.correctAnswer === optIdx ? 'checked' : ''} onchange="updateQuestion(${topicId}, ${quizId}, ${question.id}, 'correctAnswer', ${optIdx})" style="width: auto; cursor: pointer;">
                                <input type="text" class="form-control" placeholder="${question.type === 'true-false' ? opt : 'Option ' + (optIdx + 1)}" value="${opt}" ${question.type === 'true-false' ? 'readonly' : ''} onchange="updateQuestionOption(${topicId}, ${quizId}, ${question.id}, ${optIdx}, this.value)" style="${question.type === 'true-false' ? 'background: #f5f5f5; cursor: not-allowed;' : ''}">
                            </div>
                        `;
    }).join('')}
                </div>
            ` : ''}
        </div>
    `;
}

function deleteQuestion(topicId, quizId, questionId) {
    const topic = topics.find(t => t.id === topicId);
    if (!topic) return;

    const quiz = topic.quizzes.find(q => q.id === quizId);
    if (!quiz || !quiz.questions) return;

    quiz.questions = quiz.questions.filter(q => q.id !== questionId);
    quiz.expanded = true;
    renderTopicContent(topicId);
}

function updateQuestion(topicId, quizId, questionId, field, value) {
    const topic = topics.find(t => t.id === topicId);
    if (!topic) return;

    const quiz = topic.quizzes.find(q => q.id === quizId);
    if (!quiz || !quiz.questions) return;

    const question = quiz.questions.find(q => q.id === questionId);
    if (question) {
        question[field] = value;
    }
}

function updateQuestionType(topicId, quizId, questionId, type) {
    const topic = topics.find(t => t.id === topicId);
    if (!topic) return;

    const quiz = topic.quizzes.find(q => q.id === quizId);
    if (!quiz || !quiz.questions) return;

    const question = quiz.questions.find(q => q.id === questionId);
    if (question) {
        question.type = type;
        if (type === 'true-false') {
            question.options = ['True', 'False'];
            question.correctAnswer = 0;
        } else if (type === 'multiple-choice') {
            if (!question.options || question.options.length < 4) {
                question.options = ['', '', '', ''];
            }
            if (question.correctAnswer >= question.options.length) {
                question.correctAnswer = 0;
            }
        }
        quiz.expanded = true;
        renderTopicContent(topicId);
    }
}

function updateQuestionOption(topicId, quizId, questionId, optionIndex, value) {
    const topic = topics.find(t => t.id === topicId);
    if (!topic) return;

    const quiz = topic.quizzes.find(q => q.id === quizId);
    if (!quiz || !quiz.questions) return;

    const question = quiz.questions.find(q => q.id === questionId);
    if (question && question.options) {
        question.options[optionIndex] = value;
    }
}

// Video Upload Function
function handleVideoUpload(topicId, itemId, files) {
    const topic = topics.find(t => t.id === topicId);
    if (!topic) return;

    const lesson = topic.lessons.find(l => l.id === itemId);
    if (!lesson || !files || files.length === 0) return;

    const videoFile = files[0];

    const validVideoTypes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/webm', 'video/x-msvideo'];
    if (!validVideoTypes.includes(videoFile.type) && !videoFile.name.match(/\.(mp4|avi|mov|webm)$/i)) {
        alert('Please upload a valid video file (MP4, AVI, MOV, WEBM)');
        return;
    }

    const maxSize = 500 * 1024 * 1024;
    if (videoFile.size > maxSize) {
        alert('Video file size must be less than 500MB');
        return;
    }

    lesson.videoFile = {
        name: videoFile.name,
        size: videoFile.size,
        file: videoFile
    };

    lesson.expanded = true;
    renderTopicContent(topicId);
}

// Learning Material & Document Upload Function
function handleLessonMaterial(topicId, itemId, files, type) {
    const topic = topics.find(t => t.id === topicId);
    if (!topic) return;

    const lesson = topic.lessons.find(l => l.id === itemId);
    if (!lesson || !files || files.length === 0) return;

    const file = files[0];
    const maxSize = 50 * 1024 * 1024;
    if (file.size > maxSize) {
        alert('File size must be less than 50MB');
        return;
    }

    lesson.materialFile = file;
    lesson.attachmentName = file.name;
    if (type) {
        lesson.lessonType = type;
        lesson.contentType = type;
    }
    lesson.expanded = true;
    renderTopicContent(topicId);
}

function removeLessonMaterial(topicId, itemId) {
    const topic = topics.find(t => t.id === topicId);
    if (!topic) return;

    const lesson = topic.lessons.find(l => l.id === itemId);
    if (!lesson) return;

    lesson.materialFile = null;
    lesson.attachmentName = '';
    lesson.attachmentPath = '';
    lesson.expanded = true;
    renderTopicContent(topicId);
}

// Assignment File Functions
function handleAssignmentFiles(topicId, itemId, files) {
    const topic = topics.find(t => t.id === topicId);
    if (!topic) return;

    const assignment = topic.assignments.find(a => a.id === itemId);
    if (!assignment || !files || files.length === 0) return;

    if (!assignment.attachments) assignment.attachments = [];

    Array.from(files).forEach(file => {
        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            alert(`${file.name} exceeds the 10MB limit`);
            return;
        }

        assignment.attachments.push({
            id: Date.now() + Math.random(),
            name: file.name,
            size: file.size,
            file: file
        });
    });

    assignment.expanded = true;
    renderTopicContent(topicId);
}

function removeAssignmentFile(topicId, itemId, fileIndex) {
    const topic = topics.find(t => t.id === topicId);
    if (!topic) return;

    const assignment = topic.assignments.find(a => a.id === itemId);
    if (!assignment || !assignment.attachments) return;

    assignment.attachments.splice(fileIndex, 1);
    assignment.expanded = true;
    renderTopicContent(topicId);
}

function initialiseTopicItemsFromChildren(topic) {
    if (!topic) {
        return;
    }

    if (!Array.isArray(topic.items) || topic.items.length === 0) {
        topic.items = [];

        const lessonList = (topic.lessons || []).slice().sort(sortByOrderOrIndex);
        const quizList = (topic.quizzes || []).slice().sort(sortByOrderOrIndex);
        const assignmentList = (topic.assignments || []).slice().sort(sortByOrderOrIndex);

        lessonList.forEach((lesson) => {
            topic.items.push({ id: lesson.id, type: 'lesson' });
        });
        quizList.forEach((quiz) => {
            topic.items.push({ id: quiz.id, type: 'quiz' });
        });
        assignmentList.forEach((assignment) => {
            topic.items.push({ id: assignment.id, type: 'assignment' });
        });
    }

    syncTopicCollections(topic);
}

function ensureTopicItems(topic) {
    if (!topic) return;

    if (!Array.isArray(topic.items)) {
        topic.items = [];
    }

    const appendIfMissing = (id, type) => {
        if (!topic.items.find(ref => ref.id === id && ref.type === type)) {
            topic.items.push({ id, type });
        }
    };

    (topic.lessons || []).forEach(lesson => appendIfMissing(lesson.id, 'lesson'));
    (topic.quizzes || []).forEach(quiz => appendIfMissing(quiz.id, 'quiz'));
    (topic.assignments || []).forEach(assignment => appendIfMissing(assignment.id, 'assignment'));

    topic.items = topic.items.filter(ref => {
        return Boolean(findTopicItemByReference(topic, ref));
    });

    syncTopicCollections(topic);
}

function findTopicItemByReference(topic, reference) {
    if (!reference || !topic) return null;

    if (reference.type === 'lesson') {
        const found = topic.lessons.find(item => item.id === reference.id);
        if (found) {
            found.type = 'lesson';
        }
        return found || null;
    }

    if (reference.type === 'quiz') {
        const found = topic.quizzes.find(item => item.id === reference.id);
        if (found) {
            found.type = 'quiz';
        }
        return found || null;
    }

    if (reference.type === 'assignment') {
        const found = topic.assignments.find(item => item.id === reference.id);
        if (found) {
            found.type = 'assignment';
        }
        return found || null;
    }

    return null;
}

function sortByOrderOrIndex(a, b) {
    const orderA = typeof a.order !== 'undefined' ? a.order : 0;
    const orderB = typeof b.order !== 'undefined' ? b.order : 0;
    if (orderA === orderB) {
        return 0;
    }
    return orderA < orderB ? -1 : 1;
}

function initialiseTopicSortable() {
    const container = document.getElementById('curriculumContainer');
    if (!container || typeof Sortable === 'undefined') {
        return;
    }

    if (topicSortableInstance) {
        topicSortableInstance.destroy();
    }

    topicSortableInstance = Sortable.create(container, {
        handle: '.topic-drag-handle',
        animation: 200,
        onEnd: function (evt) {
            if (evt.oldIndex === evt.newIndex) {
                return;
            }
            const movedTopic = topics.splice(evt.oldIndex, 1)[0];
            topics.splice(evt.newIndex, 0, movedTopic);
            renderTopics();
        }
    });
}

function initialiseContentSortable(topicId) {
    if (typeof Sortable === 'undefined') {
        return;
    }

    const container = document.querySelector(`.topic-content-list[data-content-list="${topicId}"]`);
    if (!container) {
        return;
    }

    if (contentSortableInstances[topicId]) {
        const existing = contentSortableInstances[topicId];
        if (existing && typeof existing.destroy === 'function') {
            existing.destroy();
        }
    }

    contentSortableInstances[topicId] = Sortable.create(container, {
        handle: '.content-item-header .fa-grip-vertical',
        animation: 200,
        onEnd: function (evt) {
            if (evt.oldIndex === evt.newIndex) {
                return;
            }

            const topic = topics.find(t => t.id === topicId);
            if (!topic || !Array.isArray(topic.items)) {
                return;
            }

            const movedItem = topic.items.splice(evt.oldIndex, 1)[0];
            topic.items.splice(evt.newIndex, 0, movedItem);
            syncTopicCollections(topic);
        }
    });
}

function syncTopicCollections(topic) {
    if (!topic) return;

    const lessonMap = new Map((topic.lessons || []).map(item => [item.id, item]));
    const quizMap = new Map((topic.quizzes || []).map(item => [item.id, item]));
    const assignmentMap = new Map((topic.assignments || []).map(item => [item.id, item]));

    const newLessons = [];
    const newQuizzes = [];
    const newAssignments = [];

    (topic.items || []).forEach(ref => {
        if (ref.type === 'lesson' && lessonMap.has(ref.id)) {
            const lesson = lessonMap.get(ref.id);
            lesson.order = newLessons.length;
            newLessons.push(lesson);
            lessonMap.delete(ref.id);
        } else if (ref.type === 'quiz' && quizMap.has(ref.id)) {
            const quiz = quizMap.get(ref.id);
            quiz.order = newQuizzes.length;
            newQuizzes.push(quiz);
            quizMap.delete(ref.id);
        } else if (ref.type === 'assignment' && assignmentMap.has(ref.id)) {
            const assignment = assignmentMap.get(ref.id);
            assignment.order = newAssignments.length;
            newAssignments.push(assignment);
            assignmentMap.delete(ref.id);
        }
    });

    lessonMap.forEach(value => {
        value.order = newLessons.length;
        newLessons.push(value);
    });
    quizMap.forEach(value => {
        value.order = newQuizzes.length;
        newQuizzes.push(value);
    });
    assignmentMap.forEach(value => {
        value.order = newAssignments.length;
        newAssignments.push(value);
    });

    topic.lessons = newLessons;
    topic.quizzes = newQuizzes;
    topic.assignments = newAssignments;
}