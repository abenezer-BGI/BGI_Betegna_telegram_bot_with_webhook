<?php


namespace App\Telegram\UpdateHandlers\TelecomBill;


use App\Models\BotStatus;
use App\Models\BotUser;
use App\Models\TelecomBill\Expense;
use App\Traits\TelegramCustomTrait;
use WeStacks\TeleBot\Exception\TeleBotObjectException;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\InlineKeyboardButton;
use WeStacks\TeleBot\Objects\Keyboard\InlineKeyboardMarkup;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class BotTelecomBillCallbackHandler extends UpdateHandler
{
    use TelegramCustomTrait;

    /**
     * @inheritDoc
     */
    public static function trigger(Update $update, TeleBot $bot)
    {
        if ($update->type() === 'callback_query' and str_starts_with($update->callback_query->data, 'telecom_bill')) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     * @throws TeleBotObjectException
     */
    public function handle()
    {
        $update = $this->update;
        $callback = $update->callback_query;
        $bot_user = BotUser::query()->firstWhere('telegram_user_id', '=', $callback->message->chat->id);
        $bot_status = BotStatus::query()->firstWhere('user_id', '=', $bot_user->id);

//        Log::info('Bot_status_path: '.$bot_status->path.chr(10).' Bot_last_question: '.$bot_status->last_question.chr(10).' Bot_last_answer: '.$bot_status->last_answer.chr(10).' Bot_back_button_data: '.$this->back_button_callback_data($bot_status->path));

        if ($callback->data === 'telecom_bill') {
            $bot_status->update([
                'path' => $this->path_append($bot_status->path, 'telecom_bill'),
                'back_path' => 'root',
            ]);

            if (is_null($bot_user->service_number)) {
                $bot_status->update([
                    'last_question' => 'telecom_bill.service_number',
                ]);
                $this->sendMessage([
                    'chat_id' => $callback->message->chat->id,
                    'text' => 'Please send me your service number so that I can associate it with your telegram account for this bot.' . chr(10)
                        . 'Format: 9xxxxxxxx',
                ]);
            } else {
                $this->editMessageText([
                    'chat_id' => $callback->message->chat->id,
                    'message_id' => $callback->message->message_id,
                    'text' => 'Please select the bill YEAR you want to browse',
                    'reply_markup' => new InlineKeyboardMarkup([
                        'inline_keyboard' => [
                            [
                                new InlineKeyboardButton([
                                    'text' => now()->subYears(2)->format('Y'),
                                    'callback_data' => 'telecom_bill.year.' . now()->subYears(2)->format('Y'),
                                ]),
                                new InlineKeyboardButton([
                                    'text' => now()->subYears(1)->format('Y'),
                                    'callback_data' => 'telecom_bill.year.' . now()->subYears(1)->format('Y'),
                                ]),
                                new InlineKeyboardButton([
                                    'text' => now()->format('Y'),
                                    'callback_data' => 'telecom_bill.year.' . now()->format('Y'),
                                ]),
                            ], [
                                new InlineKeyboardButton([
                                    'text' => 'Back',
                                    'callback_data' => $bot_status->back_path,
                                ]),
                                new InlineKeyboardButton([
                                    'text' => 'Home',
                                    'callback_data' => $bot_status->root_path,
                                ]),
                            ]
                        ],
                    ]),
                ]);
            }
        }

        if (str_starts_with($callback->data, 'telecom_bill.year')) {
            $array_data = explode('.', $callback->data);
            $year = end($array_data);
            $bot_status->update([
                'last_answer' => $year,
                'path' => $this->path_append($bot_status->path, '.year'),
                'back_path' => 'telecom_bill',
            ]);
            $this->editMessageText([
                'chat_id' => $callback->message->chat->id,
                'message_id' => $callback->message->message_id,
                'text' => 'Please select the bill MONTH you want to browse',
                'reply_markup' => new InlineKeyboardMarkup([
                    'inline_keyboard' => [
                        [
                            new InlineKeyboardButton([
                                'text' => 'Jan',
                                'callback_data' => 'telecom_bill.month.January',
                            ]),
                            new InlineKeyboardButton([
                                'text' => 'Feb',
                                'callback_data' => 'telecom_bill.month.February',
                            ]),
                            new InlineKeyboardButton([
                                'text' => 'Mar',
                                'callback_data' => 'telecom_bill.month.March',
                            ]),
                            new InlineKeyboardButton([
                                'text' => 'Apr',
                                'callback_data' => 'telecom_bill.month.April',
                            ]),
                        ],
                        [
                            new InlineKeyboardButton([
                                'text' => 'May',
                                'callback_data' => 'telecom_bill.month.May',
                            ]),
                            new InlineKeyboardButton([
                                'text' => 'Jun',
                                'callback_data' => 'telecom_bill.month.June',
                            ]),
                            new InlineKeyboardButton([
                                'text' => 'Jul',
                                'callback_data' => 'telecom_bill.month.July',
                            ]),
                            new InlineKeyboardButton([
                                'text' => 'Aug',
                                'callback_data' => 'telecom_bill.month.August',
                            ]),
                        ],
                        [
                            new InlineKeyboardButton([
                                'text' => 'Sep',
                                'callback_data' => 'telecom_bill.month.September',
                            ]),
                            new InlineKeyboardButton([
                                'text' => 'Oct',
                                'callback_data' => 'telecom_bill.month.October',
                            ]),
                            new InlineKeyboardButton([
                                'text' => 'Nov',
                                'callback_data' => 'telecom_bill.month.November',
                            ]),
                            new InlineKeyboardButton([
                                'text' => 'Dec',
                                'callback_data' => 'telecom_bill.month.December',
                            ]),
                        ], [
                            new InlineKeyboardButton([
                                'text' => 'Back',
                                'callback_data' => $bot_status->back_path,
                            ]),
                            new InlineKeyboardButton([
                                'text' => 'Home',
                                'callback_data' => $bot_status->root_path,
                            ]),
                        ]
                    ],
                ]),
            ]);
        }

        if (str_starts_with($callback->data, 'telecom_bill.month')) {
            $bot_status->update([
                'path' => $this->path_append($bot_status->path, '.month'),
                'back_path' => 'telecom_bill.year',
            ]);
            $array_data = explode('.', $callback->data);
            $month = end($array_data);
            $year = $bot_status->last_answer;

            $expense = Expense::query()
                ->where('year', '=', $year)
                ->where('month', '=', $month)
                ->whereHas('service_number', function ($service_number) use ($bot_user) {
                    $service_number->where('number', '=', $bot_user->service_number);
                })
                ->selectRaw('*, sum(expense) as total')
                ->groupBy(['year', 'month'])
                ->get()
                ->first();

            $this->sendMessage([
                'chat_id' => $callback->message->chat->id,
                'text' => 'Bill expense for ' . $month . '-' . $year . ': ' . chr(10) . round($expense->total ?? 0, 2)
            ]);
        }

        // To remove that count clock icon
        $this->answerCallbackQuery();
    }

//    public function path_append($path, $text)
//    {
//        $array_path = explode('.', $path);
//        return end($array_path) === $text ? $path : $path . $text;
//    }

    public function back_button_callback_data($current_path)
    {
        $current_path_array = explode('.', $current_path);

        if (count($current_path_array) > 1) {
            array_pop($current_path_array);
            return implode('.', $current_path_array);
        }

        return 'root';
    }
}
