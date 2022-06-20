<?php


namespace App\Telegram\UpdateHandlers\DeviceInventory;


use App\Models\DeviceInventory\Computer;
use App\Models\DeviceInventory\Monitor;
use App\Models\DeviceInventory\Printer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use WeStacks\TeleBot\Interfaces\UpdateHandler;
use WeStacks\TeleBot\Objects\InlineKeyboardButton;
use WeStacks\TeleBot\Objects\Keyboard\InlineKeyboardMarkup;
use WeStacks\TeleBot\Objects\Update;
use WeStacks\TeleBot\TeleBot;

class BotDeviceInventoryUpdateHandler extends UpdateHandler
{

    /**
     * @inheritDoc
     */
    public static function trigger(Update $update, TeleBot $bot)
    {
        if ($update->type() === 'callback_query' and str_starts_with($update->callback_query->data,'device_inventory') ) {
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function handle()
    {
        $update = $this->update;
        $callback = $update->callback_query;

        if ($callback->data === 'device_inventory') {
            $this->editMessageText([
                'chat_id' => $callback->message->chat->id,
                'message_id' => $callback->message->message_id,
                'text' => 'Choose what you want to do',
                'reply_markup' => new InlineKeyboardMarkup([
                    'inline_keyboard' => [
//                            [
//                                new InlineKeyboardButton([
//                                    'text' => 'Dashboard',
//                                    'callback_data' => 'device_inventory.dashboard',
//                                ]),
//                            ],
                        [new InlineKeyboardButton([
                            'text' => 'Devices Per Site',
                            'callback_data' => 'device_inventory.device_per_site',
                        ]),
                        ],
//                            [new InlineKeyboardButton([
//                                    'text' => 'Sea Count',
//                                    'callback_data' => 'device_inventory.device_per_site',
//                                ]),
//                            ],
                        [new InlineKeyboardButton([
                            'text' => 'Unassigned Devices',
                            'callback_data' => 'device_inventory.unassigned_devices',
                        ]),
                        ],
                    ],
                ]),
            ]);
        }

        if ($callback->data === 'device_inventory.unassigned_devices') {
            $unassigned_devices = DB::connection('device_inventory')->select('SELECT serial_number, device_type,model,brand from computers where device_owner_id is null union (SELECT serial_number, device_type,model,brand from monitors where device_owner_id is null) union (SELECT serial_number, device_type,model,brand from printers where device_owner_id is null)');
            $text = 'Unassigned Devices' . chr(10);
            foreach ($unassigned_devices as $device) {
                $text .= $device->device_type . ' [' . $device->serial_number . '] ' . $device->model . chr(10);
            }

            $this->sendMessage([
                'chat_id' => $callback->message->chat->id,
                'text' => $text,
            ]);
        }

        if ($callback->data === 'device_inventory.device_per_site') {
            $this->editMessageText([
                'chat_id' => $callback->message->chat->id,
                'message_id' => $callback->message->message_id,
                'text' => 'Choose device type',
                'reply_markup' => new InlineKeyboardMarkup([
                    'inline_keyboard' => [
                        [
                            new InlineKeyboardButton([
                                'text' => 'Computer',
                                'callback_data' => 'device_inventory.device_per_site.computer',
                            ]),
                            new InlineKeyboardButton([
                                'text' => 'Monitor',
                                'callback_data' => 'device_inventory.device_per_site.monitor',
                            ]),
                            new InlineKeyboardButton([
                                'text' => 'Printer',
                                'callback_data' => 'device_inventory.device_per_site.printer',
                            ]),
                        ]
                    ],
                ]),
            ]);
        }

        if ($callback->data === 'device_inventory.device_per_site.computer') {
            $computer_data = Computer::with('site:id,display_name')->selectRaw('count(site_id) as count, device_type, site_id')->groupBy(['device_type', 'site_id'])->get();
            $text = 'Computers Data' . chr(10);
            foreach ($computer_data as $computer) {
                $text .= $computer->site->display_name . ' [' . $computer->device_type . '] = ' . $computer->count . chr(10);
            }

            $this->sendMessage([
                'chat_id' => $callback->message->chat->id,
                'text' => $text,
            ]);
        }

        if ($callback->data === 'device_inventory.device_per_site.monitor') {
            $monitor_data = Monitor::with('site:id,display_name')->selectRaw('count(site_id) as count, device_type, site_id')->groupBy(['device_type', 'site_id'])->get();
            $text = 'Monitors Data' . chr(10);
            foreach ($monitor_data as $monitor) {
                $text .= $monitor->site->display_name . ' [' . $monitor->device_type . '] = ' . $monitor->count . chr(10);
            }

            $this->sendMessage([
                'chat_id' => $callback->message->chat->id,
                'text' => $text,
            ]);
        }

        if ($callback->data === 'device_inventory.device_per_site.printer') {
            $printer_data = Printer::with('site:id,display_name')->selectRaw('count(site_id) as count, device_type, site_id')->groupBy(['device_type', 'site_id'])->get();
            $text = 'Printers Data' . chr(10);
            foreach ($printer_data as $printer) {
                $text .= $printer->site->display_name . ' [' . $printer->device_type . '] = ' . $printer->count . chr(10);
            }

            $this->sendMessage([
                'chat_id' => $callback->message->chat->id,
                'text' => $text,
            ]);
        }

        // To remove that count clock icon
        $this->answerCallbackQuery();
    }
}
