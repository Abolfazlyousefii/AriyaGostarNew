<?php

namespace App\Services\Sms;

use App\Contracts\SmsContract;
use Illuminate\Support\Facades\Log;
use Melipayamak\MelipayamakApi;
use RuntimeException;
use Throwable;

class MelipayamakSms extends SmsService implements SmsContract
{
    public function send()
    {
        $method = $this->method();
        $data   = $this->$method();

        $inputData = $data['input_data'];
        $mobile    = $this->mobile();
        $bodyId    = $data['bodyId'];
        $response  = null;

        Log::info('Melipayamak SMS request.', [
            'mobile' => $mobile,
            'bodyId' => $bodyId,
            'input_data' => $inputData,
        ]);

        try {
            $username = trim((string) option('MELIPAYAMAK_PANEL_USERNAME'));
            $password = (string) option('MELIPAYAMAK_PANEL_PASSWORD');

            if ($username === '' || trim($password) === '') {
                throw new RuntimeException('Melipayamak credentials are not configured.');
            }

            if (empty($bodyId)) {
                throw new RuntimeException('Melipayamak pattern bodyId is empty.');
            }

            $api      = new MelipayamakApi($username, $password);
            $sms      = $api->sms('soap');
            $text     = implode(';', $inputData);
            $response = $sms->sendByBaseNumber($text, $mobile, $bodyId);

            Log::info('Melipayamak SMS response.', [
                'mobile' => $mobile,
                'bodyId' => $bodyId,
                'response' => $response,
            ]);

            $responseCode = is_numeric($response) ? (int) $response : null;

            if ($responseCode === null || $responseCode <= 0) {
                $message = match ($responseCode) {
                    -110 => 'کد پترن ملی پیامک معتبر نیست یا اجازه ارسال ندارد.',
                    -104 => 'اعتبار یا دسترسی پنل ملی پیامک بررسی شود.',
                    -100 => 'نام کاربری یا رمز پنل ملی پیامک صحیح نیست.',
                    default => 'Melipayamak rejected the SMS request.',
                };

                Log::warning('Melipayamak rejected SMS.', [
                    'code' => $responseCode,
                    'bodyId' => $bodyId,
                    'mobile' => $mobile,
                ]);

                throw new RuntimeException(
                    $message . ' Response: ' .
                    (is_scalar($response) ? (string) $response : json_encode($response))
                );
            }
        } catch (Throwable $exception) {
            Log::error('Melipayamak SMS exception.', [
                'mobile' => $mobile,
                'bodyId' => $bodyId,
                'input_data' => $inputData,
                'response' => $response,
                'message' => $exception->getMessage(),
            ]);

            throw new RuntimeException(
                'Melipayamak SMS delivery failed.',
                0,
                $exception
            );
        }

        return json_encode($response);
    }

    public function verifyCode()
    {
        return [
            'bodyId'       => option('user_verify_pattern_code_melipayamak'),
            'input_data'   => [
                '0' => $this->data['code']
            ],
        ];
    }

    public function userCreated()
    {
        return [
            'bodyId'       => option('user_register_pattern_code_melipayamak'),
            'input_data'   => [
                '0'   => $this->data['fullname'],
                '1'   => $this->data['username'],
            ],
        ];
    }

    public function orderPaid()
    {
        return [
            'bodyId'       => option('order_paid_pattern_code_melipayamak'),
            'input_data'   => [
                '0' => $this->data['order_id']
            ],
        ];
    }

    public function orderDetail()
    {
        return [
            'bodyId'       => option('order_detail_pattern_code_melipayamak'),
            'input_data'   => [
                '0' => $this->data['order_id'],
                '1' => $this->data['items'],
                '2' => $this->data['order_price'],
            ],
        ];
    }

    public function trackingCode()
    {
        return [
            'bodyId'       => option('tracking_code_pattern_code_melipayamak'),
            'input_data'   => [
                '0' => $this->data['tracking_code'],
                '1' => $this->data['order_id'],
            ],
        ];
    }

    public function userOrderPaid()
    {
        return [
            'bodyId'       => option('user_order_paid_pattern_code_melipayamak'),
            'input_data'   => [
                '0' => $this->data['order_id']
            ],
        ];
    }

    public function inPersonOrder()
    {
        return [
            'bodyId'       => option('in_person_order_pattern_code_melipayamak'),
            'input_data'   => [
                '0' => $this->data['fullname']
            ],
        ];
    }

    public function walletAmountDecreased()
    {
        return [
            'bodyId'       => option('wallet_decrease_pattern_code_melipayamak'),
            'input_data'   => [
                '0' => $this->data['amount']
            ],
        ];
    }

    public function walletAmountIncreased()
    {
        return [
            'bodyId'       => option('wallet_increase_pattern_code_melipayamak'),
            'input_data'   => [
                '0' => $this->data['amount']
            ],
        ];
    }

    public function stockAmountIncreased()
    {
        return [
            'bodyId'       => option('stock_increase_pattern_code_melipayamak'),
            'input_data' => [
                0 => $this->data['product_title'],
            ],
        ];
    }
}
