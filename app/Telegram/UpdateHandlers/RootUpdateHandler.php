<?php


namespace App\Telegram\UpdateHandlers;


use App\Models\BotStatus;
use App\Models\BotUser;
use App\Telegram\Commands\StartCommand;
use App\Telegram\UpdateHandlers\eLeader\BotELeaderCallbackHandler;
use App\Telegram\UpdateHandlers\eLeader\BotELeaderUpdateHandler;
use App\Traits\TelegramCustomTrait;
use Exception;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;

class RootUpdateHandler
{
    /**
     * @var Api
     */
    public $bot;

    /**
     * @var
     */
    public $update;

    use TelegramCustomTrait;

    public function __construct(Api $bot, $update)
    {
        $this->bot = $bot;
        $this->update = $update;
    }

    public function handle()
    {
        $bot = $this->bot;
        $update = $this->update;

        try {
            if ($this->update->isType("callback_query")) {
                $message = $this->update->callback_query->message;
                $bot_user = BotUser::query()->firstWhere('telegram_user_id', '=', $this->update->callback_query->message->chat->id);
                $bot_status = BotStatus::query()->firstWhere('user_id', '=', $bot_user->id);
                $callbackData = $this->update->callback_query->data;
                switch ($callbackData) {
                    case 'root':
                        (new StartCommand())->handle();
                        break;
                    case 'eLeader':
                        (new BotELeaderCallbackHandler())->request_phone_number($bot, $bot_user, $bot_status, $message, $update);
                        break;
                    case 'eLeader.enqu_amount':
                        (new BotELeaderCallbackHandler())->send_enqu_amount($bot, $bot_user, $message);
                        break;
                    case 'eLeader.client_info':
                        (new BotELeaderCallbackHandler())->send_client_info($bot, $bot_user, $message, $update);
                        break;
                    case 'eLeader.visit_data':
                        (new BotELeaderCallbackHandler())->visit_info($bot, $bot_user, $message);
                        break;
                    case 'eLeader.customer_service':
                        (new BotELeaderCallbackHandler())->customer_service_contact($bot, $message);
                        break;
                    case 'eLeader.send_enqu_items':
                        (new BotELeaderCallbackHandler())->send_enqu_items($bot, $message);
                        break;
                    default:
                        $this->error_message($bot, $update, 'amharic');
                        break;
                }

            } elseif ($this->update->isType("message")) {
                $message = $this->update->message;
                $bot_user = BotUser::query()->firstWhere('telegram_user_id', '=', $this->update->message->chat->id);
                $bot_status = BotStatus::query()->firstWhere('user_id', '=', $bot_user->id);

                switch ($bot_status->last_question) {
                    case 'otp_confirmation':
                        (new BotELeaderUpdateHandler())->otp_confirmation($bot, $bot_user, $bot_status, $update);
                        break;
                    case 'eLeader_phone_number_request':
                        (new BotELeaderUpdateHandler())->phone_number_request($bot, $bot_user, $bot_status, $update);
                        break;
                    default:
                        switch ($message->text) {
                            case '????  ????????? ?????????':
                                (new BotELeaderCallbackHandler())->send_enqu_amount($bot, $bot_user, $message);
                                break;
                            case '??????  ????????? ?????????':
                                (new BotELeaderCallbackHandler())->send_client_info($bot, $bot_user, $message, $update);
                                break;
                            case '??????  ??????????????? ?????????':
                                (new BotELeaderCallbackHandler())->visit_info($bot, $bot_user, $message);
                                break;
                            case '????  ??????????????? ??????????????????':
                                (new BotELeaderCallbackHandler())->customer_service_contact($bot, $message);
                                break;
                            default:
                                $this->error_message($bot, $update, 'amharic');
                                break;
                        }
                        break;
                }
            }
        } catch (Exception $e) {
            Log::error('Line: ' . $e->getLine() . ' File:' . $e->getFile() . 'Message: ' . $e->getMessage());
        }

    }
}
