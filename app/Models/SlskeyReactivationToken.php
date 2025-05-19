<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlskeyReactivationToken extends Model
{
    use HasFactory;

    /**
     * Fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'slskey_user_id',
        'slskey_group_id',
        'token',
        'expiration_date',
        'used',
        'used_date',
        'created_from_mail_activation',
        'recipient_email',
        'created_at',
    ];

    /**
     * Get the SlskeyUser that the SlskeyReactivationToken belongs to.
     *
     * @return BelongsTo
     */
    public function slskeyUser(): BelongsTo
    {
        return $this->belongsTo(SlskeyUser::class);
    }

    /**
     * Get the SlskeyGroup that the SlskeyReactivationToken belongs to.
     *
     * @return BelongsTo
     */
    public function slskeyGroup(): BelongsTo
    {
        return $this->belongsTo(SlskeyGroup::class);
    }

    /**
     * Create a new token
     *
     * @param integer $slskeyUserId
     * @param SlskeyGroup $slskeyGroup
     * @param string $recipientEmail
     * @param boolean $createdFromMailActivation
     * @return SlskeyReactivationToken
     */
    public static function createToken(int $slskeyUserId, SlskeyGroup $slskeyGroup, string $recipientEmail, bool $createdFromMailActivation = false): SlskeyReactivationToken
    {
        $token = bin2hex(random_bytes(8));
        $tokenExpirationDate = now()->addDays($slskeyGroup->webhook_token_reactivation_days_token_validity);

        $slskeyReactivationToken = SlskeyReactivationToken::create([
            'slskey_user_id' => $slskeyUserId,
            'slskey_group_id' => $slskeyGroup->id,
            'recipient_email' => $recipientEmail,
            'token' => $token,
            'expiration_date' => $tokenExpirationDate,
            'created_from_mail_activation' => $createdFromMailActivation,
            'used' => false,
            'used_date' => null,
        ]);

        // Create URL from token, use .env APP_URL as base
        return $slskeyReactivationToken;
    }

    /**
     * Get link from token
     *
     * @return string
     */
    public function getLinkFromToken(): string
    {
        return config('app.url').'/reactivate/'.$this->token;
    }

    /**
     * Set token as used
     *
     * @return SlskeyReactivationToken
     */
    public function setUsed(): SlskeyReactivationToken
    {
        $this->used = true;
        $this->used_date = now();
        $this->save();

        return $this;
    }

    /**
     * Check if token is expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return now()->gt($this->expiration_date);
    }
}
