<?php

namespace Harvoline\OrderNotification\Traits;

use App\Models\OrderStatus;
use Illuminate\Support\Facades\Event;
use Harvoline\OrderNotification\Events\OrderStatusUpdated;

trait OrderNotificationTrait
{
	public static function bootOrderNotificationTrait()
	{
        // info('here');
        static::updated(function ($model) {
            $targetColumn = $model->updatedColumn ?? 'status';

            if (isset($model->$targetColumn)) {
                // info('Target col: ' . $targetColumn);

                // info('isDirty: ' . $model->isDirty($targetColumn));
                // info('getOriginal: ' . $model->getOriginal($targetColumn));
                // info('status: ' . $model->$targetColumn);

                if ($model->isDirty($targetColumn)
                    && $model->$targetColumn != $model->getOriginal($targetColumn)
                ) {
                    // event(new OrderStatusUpdated($model));
                    info('Dispatch event');
                    Event::dispatch(new OrderStatusUpdated($model, $targetColumn, $model->getOriginal($targetColumn)));
                }
            }
        });
	}

}
