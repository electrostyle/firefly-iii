<?php
/**
 * Budget.php
 * Copyright (c) 2019 james@firefly-iii.org
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace FireflyIII\Models;

use Carbon\Carbon;
use Eloquent;
use FireflyIII\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Budget.
 *
 * @property int                                                                                   $id
 * @property string                                                                                $name
 * @property bool                                                               $active
 * @property int                                                                $user_id
 * @property-read string                                                        $email
 * @property bool                                                               encrypted
 * @property Collection                                                         budgetlimits
 * @property int                                                                $order
 * @property Carbon                                                             created_at
 * @property Carbon                                                             updated_at
 * @property User                                                               $user
 * @property \Illuminate\Support\Carbon|null                                    $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|TransactionJournal[] $transactionJournals
 * @property-read \Illuminate\Database\Eloquent\Collection|Transaction[]        $transactions
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|Budget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Budget newQuery()
 * @method static Builder|Budget onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Budget query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereEncrypted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereUserId($value)
 * @method static Builder|Budget withTrashed()
 * @method static Builder|Budget withoutTrashed()
 * @mixin Eloquent
 */
class Budget extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts
        = [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'active'     => 'boolean',
            'encrypted'  => 'boolean',
        ];
    /** @var array Fields that can be filled */
    protected $fillable = ['user_id', 'name', 'active', 'order'];
    /** @var array Hidden from view */
    protected $hidden = ['encrypted'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @param string $value
     *
     * @throws NotFoundHttpException
     * @return Budget
     */
    public static function routeBinder(string $value): Budget
    {
        if (auth()->check()) {
            $budgetId = (int) $value;
            /** @var User $user */
            $user = auth()->user();
            /** @var Budget $budget */
            $budget = $user->budgets()->find($budgetId);
            if (null !== $budget) {
                return $budget;
            }
        }
        throw new NotFoundHttpException;
    }

    /**
     * @codeCoverageIgnore
     * @return HasMany
     */
    public function autoBudgets(): HasMany
    {
        return $this->hasMany(AutoBudget::class);
    }

    /**
     * @codeCoverageIgnore
     * @return HasMany
     */
    public function budgetlimits(): HasMany
    {
        return $this->hasMany(BudgetLimit::class);
    }


    /**
     * @codeCoverageIgnore
     * @return MorphMany
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * @codeCoverageIgnore
     * @return BelongsToMany
     */
    public function transactionJournals(): BelongsToMany
    {
        return $this->belongsToMany(TransactionJournal::class, 'budget_transaction_journal', 'budget_id');
    }

    /**
     * @codeCoverageIgnore
     * @return BelongsToMany
     */
    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'budget_transaction', 'budget_id');
    }

    /**
     * @codeCoverageIgnore
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
