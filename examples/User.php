<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class User extends Model
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'fcm_token',
    ];

    /**
     * Route notifications for the FCM channel.
     */
    public function routeNotificationForFcm($notification)
    {
        return $this->fcm_token;
    }

    /**
     * Get multiple FCM tokens if user has multiple devices
     */
    public function fcmTokens()
    {
        return $this->hasMany(UserFcmToken::class);
    }

    /**
     * Route notifications for FCM channel with multiple tokens
     */
    public function routeNotificationForFcmMultiple($notification)
    {
        return $this->fcmTokens()->pluck('token')->toArray();
    }
}
