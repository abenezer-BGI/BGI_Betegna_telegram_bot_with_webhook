<?php


namespace App\Telegram\UpdateHandlers\eLeader;


use App\Models\ELeader;
use App\Traits\TelegramCustomTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Nette\Utils\Random;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;
use WeStacks\TeleBot\Objects\KeyboardButton;
use WeStacks\TeleBot\Objects\ReplyKeyboardMarkup;

class BotELeaderUpdateHandler
{
    use TelegramCustomTrait;

    /**
     * To confirm the phone number provided is the from the owner by sending OTP
     * @param Api $bot
     * @param Builder|Model $bot_user
     * @param Builder|Model $bot_status
     * @param Update $update
     * @throws
     */
    public function otp_confirmation(Api $bot, $bot_user, $bot_status, Update $update)
    {
        $eLeaderObjectFromDB = collect(DB::connection('eLeader')->select("SELECT TOP (1000) [ID] ,[ObjectID] ,[TaskDefID] ,[FieldID] ,[FieldCode] ,[FieldName] ,[FieldValue] ,[ExportDate] FROM [ELeader_DB].[dbo].[_tbEleaderExportObjectParameters] where [_tbEleaderExportObjectParameters].FieldCode = 'OBJ_PARAM_7774424' and [_tbEleaderExportObjectParameters].FieldName='SMS phone number' and [_tbEleaderExportObjectParameters].FieldValue = '" . $bot_user->service_number . "'"));
        if ($update->message->text === $bot_status->last_answer) {
            if ($eLeaderObjectFromDB->isNotEmpty()) {
                $fidelityDataFromDB = collect(DB::connection('eLeader')->select("SELECT TOP (1000) [ID] ,[ObjectID] ,[TaskDefID] ,[FieldID] ,[FieldCode] ,[FieldName] ,[FieldValue] ,[ExportDate] FROM [ELeader_DB].[dbo].[_tbEleaderExportObjectParameters] where ObjectID= '" . $eLeaderObjectFromDB->first()->ObjectID . "' and (FieldCode='OBJ_PARAM_EarnedPoints' or FieldCode='OBJ_PARAM_Fidelity_ID' or FieldCode='OBJ_PARAM_BGIID') and (FieldName ='Earned points' or FieldName='Fidelity ID' or FieldName='BGI ID')"));

                $bot_status->update([
                    'last_question' => '',
                    'last_answer' => '',
                    'path' => $this->path_append($bot_status->path, '/otp_confirmation'),
                ]);

                ELeader::query()->updateOrCreate(
                    [
                        'fidelity_id' => $fidelityDataFromDB->where('FieldCode', 'OBJ_PARAM_Fidelity_ID')->first()->FieldValue,
                    ], [
                        'client_name' => '',
                        'bgi_id' => $fidelityDataFromDB->where('FieldCode', 'OBJ_PARAM_BGIID')->first()->FieldValue,
                        'user_id' => $bot_user->id,
                        'phone_number' => $bot_user->service_number,
                    ]
                );
                $this->eLeader_starting_menu($update, $bot_status, $bot);
            }
        } else {
            $bot->sendMessage([
                'chat_id' => $update->message->chat->id,
                'text' => 'ያስገቡት ቁጥር እና እኛ የላክነው ቁጥር አይመሳሰሉም። እባክዎን ትክክለኛውን ቁጥር ያስገቡ።',
            ]);
        }
    }

    /**
     * Displays the eLeader starting menu
     * @param Update $update
     * @param Builder|Model $bot_status
     * @param Api $bot
     * @throws TelegramSDKException
     * @throws \WeStacks\TeleBot\Exceptions\TeleBotException
     */
    public function eLeader_starting_menu($update, $bot_status, $bot)
    {

//        $keyboard = Keyboard::make()
//            ->inline()
//            ->row(
//                    Keyboard::inlineButton([
//                        'text' => '💎  እንቁ ብዛት',
//                        'callback_data' => 'eLeader.enqu_amount',
//                    ]),
//                    Keyboard::inlineButton([
//                        'text' => 'ℹ️  የቤቴ መረጃ',
//                        'callback_data' => 'eLeader.client_info',
//                    ]),
//                    Keyboard::inlineButton([
//                        'text' => 'ℹ️  የጉብኝት መረጃ',
//                        'callback_data' => 'eLeader.visit_data',
//                    ]),
//                    Keyboard::inlineButton([
//                        'text' => '📞  ደንበኞች አገልግሎት',
//                        'callback_data' => 'eLeader.customer_service',
//                    ])
//            )->setResizeKeyboard(true);
//
//        $response = $bot->sendMessage([
//            'chat_id' => $update->message->chat->id ?? $update->callback_query->message->chat->id,
//            'text' => "ውድ የቢ.ጂ.አይ ቤተኛ ደንበኛችን እንኳን ወደ ቢ.ጂ.አይ ቤተኛ ቴሌግራም ቦታችን በሰላም መጡ።" . chr(10) . "ቢ.ጂ.አይ ቤተኛን በተመለከተ ምን ማወቅ ይፈልጋሉ?",
//            'reply_markup' => $keyboard
//        ]);

        $bot->sendMessage([
            'chat_id' => $update->message->chat->id ?? $update->callback_query->message->chat->id,
            'text' => 'ውድ የቢ.ጂ.አይ ቤተኛ ደንበኛችን እንኳን ወደ ቢ.ጂ.አይ ቤተኛ ቴሌግራም ቦታችን በሰላም መጡ።' . chr(10) .
                'ቢ.ጂ.አይ ቤተኛን በተመለከተ ምን ማወቅ ይፈልጋሉ?',
            'reply_markup' => new ReplyKeyboardMarkup([
                'keyboard' => [
                    [
                        new KeyboardButton([
                            'text' => '💎  እንቁ ብዛት',
//                            'callback_data' => 'eLeader.enqu_amount',
                        ]),
                        new KeyboardButton([
                            'text' => 'ℹ️  የቤቴ መረጃ',
//                           'callback_data' => 'eLeader.client_info',
                        ]),
                    ],
                    [
                        new KeyboardButton([
                            'text' => 'ℹ️  የጉብኝት መረጃ',
                            'callback_data' => 'eLeader.visit_data',
                        ]),
                        new KeyboardButton([
                            'text' => '📞  ደንበኞች አገልግሎት',
//                            'callback_data' => 'eLeader.customer_service',
                        ]),
                    ],
                ],
                'resize_keyboard' => true,
                'input_field_placeholder'=> 'ቢ.ጂ.አይ ቤተኛ',
            ]),
        ]);
    }

    /**
     * To confirm the phone number sent has an eLeader data
     * @param Api $bot
     * @param Builder|Model $bot_user
     * @param Builder|Model $bot_status
     * @param Update $update
     * @throws TelegramSDKException
     */
    public function phone_number_request(Api $bot, $bot_user, $bot_status, Update $update)
    {
        if (preg_match('/^[0-9]+$/', $update->message->text) and strlen($update->message->text) === 10) {
            $phone_number = ltrim($update->message->text, '0');
            $eLeaderObject = collect(DB::connection('eLeader')->select("SELECT TOP (1000) [ID] ,[ObjectID] ,[TaskDefID] ,[FieldID] ,[FieldCode] ,[FieldName] ,[FieldValue] ,[ExportDate] FROM [ELeader_DB].[dbo].[_tbEleaderExportObjectParameters] where [_tbEleaderExportObjectParameters].FieldCode = 'OBJ_PARAM_7774424' and [_tbEleaderExportObjectParameters].FieldName='SMS phone number' and [_tbEleaderExportObjectParameters].FieldValue = '" . $phone_number . "'"));
            if ($eLeaderObject->isNotEmpty()) {
                $bot_status->update([
                    'path' => $this->path_append($bot_status->path, '/phone_number_received'),
                    'last_question' => 'otp_confirmation',
                    'back_path' => 'eLeader',
                    'last_answer' => $update->message->text,
                ]);
                $bot_user->update([
                    'service_number' => $update->message->text,
                ]);

                $otp_code = Random::generate(6, '0-9');
                $otp_message = 'BGI+Code:+' . $otp_code;
                $url = 'http://10.10.1.59:9501/api?action=sendmessage&username=' . env('OZEKING_USERNAME', 'admin') . '&password=' . env('OZEKING_PASSWORD', 'admin') . '&recipient=' . $bot_status->last_answer . '&messagetype=SMS:TEXT&messagedata=' . $otp_message;

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_exec($curl);

                Log::info($otp_message);
                $keyboard = Keyboard::make()
                    ->inline()
                    ->row(
                        Keyboard::inlineButton([
                            'text' => '<< ተመለስ',
                            'callback_data' => $bot_status->back_path,
                        ])
                    );
                $bot->sendMessage([
                    'chat_id' => $update->message->chat->id,
                    'text' => 'ስልክዎን ስለላኩልን በጣም እናመሰግናለን።' . chr(10) .
                        'ያስገቡት ስልክ ቁጥር የእርስዎ እንደሆነ ለማረጋገጥ አጭር የጽሁፍ መልእክት ልከንበታል።' . chr(10) .
                        'መልእክቱ ሲደርሶት በውስጡ የተካተተውን የሚስጥር ቁጥር ይላኩልን።',
                    'reply_markup' => $keyboard,
                ]);

                $bot_status->update([
                    'path' => $this->path_append($bot_status->path, '/otp_sent'),
                    'last_answer' => $otp_code,
                ]);
            } else {
                $keyboard = Keyboard::make()
                    ->inline()
                    ->row(
                        Keyboard::inlineButton([
                            'text' => '<< ተመለስ',
                            'callback_data' => $bot_status->back_path,
                        ])
                    );

                $bot->sendMessage([
                    'chat_id' => $update->message->chat->id,
                    'text' => 'ይቅርታ! ባስገቡት የስልክ ቁጥር የተመዘገብ ቤት የለም።',
                    'reply_markup' => $keyboard,
                ]);
            }
        } else {
            $this->error_message($bot, $update, 'amharic');
        }
    }
}

