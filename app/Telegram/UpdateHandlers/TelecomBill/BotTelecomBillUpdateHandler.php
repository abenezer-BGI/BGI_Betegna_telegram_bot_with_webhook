<?php


namespace App\Telegram\UpdateHandlers\TelecomBill;


use App\Models\BotStatus;
use App\Models\BotUser;
use App\Traits\TelegramCustomTrait;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\InlineKeyboardButton;
use WeStacks\TeleBot\Objects\Keyboard\InlineKeyboardMarkup;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class BotTelecomBillUpdateHandler extends UpdateHandler
{
    use TelegramCustomTrait;
    /**
     * @inheritDoc
     */
    public static function trigger(Update $update, TeleBot $bot)
    {
        if ($update->type() !== 'callback_query' and isset($update->message)) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     * @throws \WeStacks\TeleBot\Exception\TeleBotObjectException
     */
    public function handle()
    {
        $update = $this->update;
        $message = $update->message;
        $bot_user = BotUser::query()->firstWhere('telegram_user_id', '=', $message->from->id);
        $bot_status = BotStatus::query()->firstWhere('user_id', '=', $bot_user->id);

        if ($bot_status->last_question === 'telecom_bill.service_number') {
            $bot_status->update([
                'path' => $this->path_append($bot_status->path, 'service_number'),
                'back_path'=>'root',
            ]);
            if (preg_match('/^[0-9]+$/', $update->message->text)) {
                $bot_user->update([
                    'service_number' => $update->message->text,
                ]);
                $bot_status->update([
                    'last_question' => null,
                ]);
                $this->sendMessage([
                    'chat_id' => $message->chat->id,
                    'text' => 'Success! I have successfully received your service number.' . chr(10) .
                        'Now your telegram account is connected to the service number ' . $bot_user->service_number,
                ]);

                $this->editMessageText([
                    'chat_id' => $this->update->message->chat->id,
                    'message_id' => $this->update->message->message_id,
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
            } else {
                $this->sendMessage([
                    'chat_id' => $message->chat->id,
                    'text' => 'Error! Please send a valid phone number as per requested format.' . chr(10) .
                        'Format: 9xxxxxxxx',
                ]);
            }
        }
    }

//    public function path_append($path, $text)
//    {
//        $array_path = explode('.', $path);
//        return end($array_path) === $text ? $path : $path . $text;
//    }
}
