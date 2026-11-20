<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Contracts\Auth\MustVerifyEmail;

/**
 * User Model
 *
 * Represents an authenticated user (Admin, Instructor, Scholar) in Maarifazone.
 *
 * @package App\Models
 * @author Renatus Bernardo
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            \Illuminate\Support\Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $this->getKey(),
                'hash' => sha1($this->getEmailForVerification()),
            ]
        );

        \Illuminate\Support\Facades\Mail::to($this)->send(new \App\Mail\VerifyEmailMail($this, $verificationUrl));
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_approved',
        'is_blocked',
        'blocked_at',
        'blocked_reason',
        'force_password_reset',
        'password_changed_at',
        'failed_login_attempts',
        'lockout_count',
        'locked_until',
        'active_session_id',
        'last_activity_at',
        'bio',
        'phone',
        'address',
        'specialization',
        'years_experience',
        'previous_experience',
        'highest_degree',
        'field_of_study',
        'certifications',
        'profile_photo',
        'resume',
        'linkedin',
        'twitter',
        'facebook',
        'youtube',
        'approval_reason',
        'rejection_reason',
        'balance',
        'stripe_connect_account_id',
        'stripe_connect_onboarded',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
            'is_blocked' => 'boolean',
            'blocked_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'locked_until' => 'datetime',
            'last_activity_at' => 'datetime',
            'force_password_reset' => 'boolean',
            'balance' => 'decimal:2',
            'stripe_connect_onboarded' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include pending instructors.
     */
    public function scopePendingInstructors($query)
    {
        return $query->where('role', 'instructor')->where('is_approved', false);
    }

    /**
     * Scope a query to only include approved instructors.
     */
    public function scopeApprovedInstructors($query)
    {
        return $query->where('role', 'instructor')->where('is_approved', true);
    }

    /**
     * Scope a query to only include students / scholars.
     */
    public function scopeStudents($query)
    {
        return $query->where('role', 'student');
    }

    /**
     * Scope a query to only include active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_blocked', false);
    }

    /**
     * Check if user is an Administrator.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->hasRole('admin');
    }

    /**
     * Check condition: Is super admin / full admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * All admins in Maarifazone have full global system oversight.
     */
    public function isMultiBranchAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function effectiveBranchId(?int $requestedBranchId = null): ?int
    {
        return null;
    }

    /**
     * Human-friendly label for the user's role.
     */
    public function primaryRoleLabel(): string
    {
        return match ($this->role) {
            'admin'      => 'Administrator',
            'instructor' => 'Instructor',
            'student'    => 'Scholar',
            default      => ucfirst($this->role ?? 'User'),
        };
    }

    /**
     * Dedicated dashboard profile key.
     */
    public function primaryDashboardProfile(): string
    {
        return match ($this->role) {
            'instructor' => 'instructor',
            'student'    => 'student',
            default      => 'admin',
        };
    }

    /**
     * Human-readable scope summary.
     */
    public function dashboardScopeDescription(): string
    {
        return match ($this->role) {
            'instructor' => 'Instructor Portal',
            'student'    => 'Scholar Portal',
            default      => 'Full System Administration',
        };
    }

    /**
     * Check if an admin user has a specific granular permission.
     */
    public function hasAdminPermission(string $permission): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        try {
            return $this->hasPermissionTo($permission);
        } catch (\Throwable) {
            return false;
        }
    }

    public function isInstructor(): bool
    {
        return $this->role === 'instructor';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isApprovedInstructor(): bool
    {
        return $this->isInstructor() && $this->is_approved;
    }

    /**
     * Get the courses for the instructor.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    /**
     * Get the enrollments for the scholar.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the lesson progress records for the scholar.
     */
    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /**
     * Get the reviews written by the scholar.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Eloquent hasMany relationship for Account deletion requests.
     */
    public function accountDeletionRequests(): HasMany
    {
        return $this->hasMany(AccountDeletionRequest::class);
    }

    /**
     * Eloquent hasOne relationship for Notification settings.
     */
    public function notificationSettings(): HasOne
    {
        return $this->hasOne(UserNotificationSetting::class);
    }

    /**
     * Eloquent hasMany relationship for Instructor withdrawals.
     */
    public function instructorWithdrawals(): HasMany
    {
        return $this->hasMany(InstructorWithdrawal::class, 'instructor_id');
    }

    /**
     * Eloquent hasMany relationship for Community questions.
     */
    public function communityQuestions(): HasMany
    {
        return $this->hasMany(CommunityQuestion::class);
    }

    /**
     * Eloquent hasMany relationship for Community answers.
     */
    public function communityAnswers(): HasMany
    {
        return $this->hasMany(CommunityAnswer::class);
    }

    /**
     * Get conversations where user is the student.
     */
    public function studentConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'student_id');
    }

    /**
     * Get conversations where user is the instructor.
     */
    public function instructorConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'instructor_id');
    }
}
