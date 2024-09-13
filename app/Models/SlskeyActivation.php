<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlskeyActivation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'slskey_user_id',
        'slskey_group_id',
        'remark',
        'activated',
        'activation_date',
        'deactivation_date',
        'expiration_date',
        'expiration_disabled',
        'blocked',
        'blocked_date',
        'reminded',
        'webhook_activation_mail',
        'member_educational_institution',
    ];

    /**
     * Get Slskey User
     *
     * @return BelongsTo
     */
    public function slskeyUser(): BelongsTo
    {
        return $this->belongsTo(SlskeyUser::class);
    }

    /**
     * Get Slskey Group
     *
     * @return BelongsTo
     */
    public function slskeyGroup(): BelongsTo
    {
        return $this->belongsTo(SlskeyGroup::class);
    }

    /**
     * Scope with Slskey User and Slskey Group
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithSlskeyUserAndSlskeyGroup(Builder $query): Builder
    {
        return $query->with(['slskeyUser:primary_id,id', 'slskeyGroup:slskey_code,id']);
    }

    /**
     * Set Activated
     *
     * @param ?Carbon $activationDate
     * @param ?Carbon $expirationDate
     * @return self
     */
    public function setActivated(?Carbon $activationDate, ?Carbon $expirationDate): self
    {
        $this->activated = true;
        $this->activation_date = $activationDate;
        $this->expiration_date = $expirationDate;
        $this->deactivation_date = null;
        $this->blocked = false;
        $this->blocked_date = null;
        $this->reminded = false;
        $this->save();

        return $this;
    }

    /**
     * Set Deactivated
     *
     * @param string|null $remark
     * @return self
     */
    public function setDeactivated(?string $remark): self
    {
        $this->activated = false;
        $this->deactivation_date = now();
        $this->activation_date = null;
        $this->expiration_date = null;
        $this->expiration_disabled = false;
        $this->blocked = false;
        $this->blocked_date = null;
        $this->remark = $remark;
        $this->save();

        return $this;
    }

    public function setBlocked(?string $remark): self
    {
        $this->activated = false;
        $this->deactivation_date = null;
        $this->activation_date = null;
        $this->expiration_date = null;
        $this->blocked = true;
        $this->blocked_date = now();
        $this->remark = $remark;
        $this->save();

        return $this;
    }

    /**
     * Set Unblocked
     *
     * @param string|null $remark
     * @return self
     */
    public function setUnblocked(?string $remark): self
    {
        $this->activated = false;
        $this->deactivation_date = null;
        $this->activation_date = null;
        $this->expiration_date = null;
        $this->blocked = false;
        $this->blocked_date = null;
        $this->remark = $remark;
        $this->save();

        return $this;
    }

    /**
     * Set Remark
     *
     * @param string $remark
     * @return self
     */
    public function setRemark(string $remark): self
    {
        $this->remark = $remark;
        $this->save();

        return $this;
    }

    /**
     * Remove Remark
     *
     * @return self
     */
    public function removeRemark(): self
    {
        $this->remark = null;
        $this->save();

        return $this;
    }

    /**
     * Set Activation Date
     *
     * @param Carbon $newActivationDate
     * @return self
     */
    public function setActivationDate(Carbon $newActivationDate): self
    {
        $this->activation_date = $newActivationDate;
        $this->save();

        return $this;
    }

    /**
     * Set Expiration Disabled
     *
     * @return self
     */
    public function setExpirationDisabled(): self
    {
        $this->expiration_disabled = true;
        $this->expiration_date = null;
        $this->save();

        return $this;
    }

    /**
     * Set Expiration Enabled
     *
     * @param Carbon $newExpirationDate
     * @return self
     */
    public function setExpirationEnabled(Carbon $newExpirationDate): self
    {
        $this->expiration_disabled = false;
        $this->expiration_date = $newExpirationDate;
        $this->save();

        return $this;
    }

    /**
     * Set Expiration Date
     *
     * @param Carbon $newExpirationDate
     * @return self
     */
    public function setExpirationDate(Carbon $newExpirationDate): self
    {
        $this->expiration_date = $newExpirationDate;
        $this->save();

        return $this;
    }

    /**
     * Set Webhook Activation Mail
     *
     * @param string $webhookActivationMail
     * @return self
     */
    public function setWebhookActivationMail(string $webhookActivationMail): self
    {
        $this->webhook_activation_mail = $webhookActivationMail;
        $this->save();

        return $this;
    }

    /**
     * Remove Webhook Activation Mail
     *
     * @return self
     */
    public function removeWebhookActivationMail(): self
    {
        $this->webhook_activation_mail = null;
        $this->save();

        return $this;
    }

    /**
     * Set Reminded
     *
     * @return self
     */
    public function setReminded(): self
    {
        $this->reminded = true;
        $this->save();

        return $this;
    }

    /**
     * Set Member Educational Institution
     *
     * @param bool $memberEducationalInstitution
     * @return self
     */
    public function setMemberEducationalInstitution(bool $memberEducationalInstitution): self
    {
        $this->member_educational_institution = $memberEducationalInstitution;
        $this->save();

        return $this;
    }
}
