<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * PaymentSeeder
 * Thanh toán cho subscription và hợp đồng PT
 * Cột: id, invoice_number, user_id, subscription_id, payable_id, payable_type,
 *       amount, payment_date, payment_method, status, promotion_id,
 *       payment_confirmed, note, created_at, updated_at
 */
class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $payments = [];
        $invoiceCounter = 1;

        $paymentMethods = ['cash', 'transfer', 'momo', 'vnpay', 'cash', 'transfer', 'momo'];

        // ============================================================
        // 1. Thanh toán gói tập (Member Subscriptions)
        // ============================================================
        $subscriptions = DB::table('member_subscriptions')->orderBy('id')->get();
        foreach ($subscriptions as $idx => $sub) {
            $status  = 'paid';
            $confirmed = true;
            $note    = null;

            if ($sub->status === 'pending') {
                $status    = 'pending';
                $confirmed = false;
                $note      = 'Chờ nhân viên xác nhận thanh toán.';
            } elseif ($sub->status === 'cancelled') {
                // Thanh toán đã thực hiện nhưng hủy → refund
                $status = rand(0, 1) ? 'refunded' : 'paid';
            }

            $method = $paymentMethods[$idx % count($paymentMethods)];
            $invoiceNo = 'INV-' . date('Ymd', strtotime($sub->start_date)) . '-' . str_pad($invoiceCounter++, 5, '0', STR_PAD_LEFT);

            $payments[] = [
                'invoice_number'   => $invoiceNo,
                'user_id'          => $sub->user_id,
                'subscription_id'  => $sub->id,
                'payable_id'       => $sub->id,
                'payable_type'     => 'App\\Models\\MemberSubscription',
                'amount'           => $sub->price,
                'payment_date'     => $sub->start_date,
                'payment_method'   => $method,
                'status'           => $status,
                'promotion_id'     => $sub->promotion_id,
                'payment_confirmed'=> $confirmed ? 1 : 0,
                'note'             => $note,
                'created_at'       => $sub->start_date . ' 10:30:00',
                'updated_at'       => now(),
            ];
        }

        // ============================================================
        // 2. Thanh toán hợp đồng PT
        // ============================================================
        $ptContracts = DB::table('pt_contracts')->orderBy('id')->get();
        foreach ($ptContracts as $idx => $contract) {
            $status    = 'paid';
            $confirmed = true;
            $note      = null;

            if ($contract->status === 'pending') {
                $status    = 'pending';
                $confirmed = false;
                $note      = 'Chờ xác nhận thanh toán hợp đồng PT.';
            } elseif ($contract->status === 'cancelled') {
                $status = 'refunded';
                $note   = 'Hợp đồng PT bị hủy, hoàn tiền 50%.';
            }

            $method    = $paymentMethods[($idx + 2) % count($paymentMethods)];
            $invoiceNo = 'INV-' . date('Ymd', strtotime($contract->start_date)) . '-' . str_pad($invoiceCounter++, 5, '0', STR_PAD_LEFT);

            $payments[] = [
                'invoice_number'   => $invoiceNo,
                'user_id'          => $contract->user_id,
                'subscription_id'  => null,
                'payable_id'       => $contract->id,
                'payable_type'     => 'App\\Models\\PtContract',
                'amount'           => $contract->price,
                'payment_date'     => $contract->start_date,
                'payment_method'   => $method,
                'status'           => $status,
                'promotion_id'     => null,
                'payment_confirmed'=> $confirmed ? 1 : 0,
                'note'             => $note,
                'created_at'       => $contract->start_date . ' 11:00:00',
                'updated_at'       => now(),
            ];
        }

        // ============================================================
        // 3. Thanh toán lớp học nhóm có phí
        // ============================================================
        $paidClasses = DB::table('classes')->where('class_cost', '>', 0)->get();
        if ($paidClasses->isNotEmpty()) {
            $registrations = DB::table('class_registrations')
                ->whereIn('class_id', $paidClasses->pluck('id'))
                ->where('status', '!=', 'cancelled')
                ->orderBy('id')
                ->get();

            foreach ($registrations as $idx => $reg) {
                $class  = $paidClasses->firstWhere('id', $reg->class_id);
                $method = $paymentMethods[$idx % count($paymentMethods)];
                $invoiceNo = 'INV-' . date('Ymd', strtotime($reg->registration_date)) . '-' . str_pad($invoiceCounter++, 5, '0', STR_PAD_LEFT);

                $payments[] = [
                    'invoice_number'   => $invoiceNo,
                    'user_id'          => $reg->user_id,
                    'subscription_id'  => null,
                    'payable_id'       => $reg->class_id,
                    'payable_type'     => 'App\\Models\\GymClass',
                    'amount'           => $class->class_cost,
                    'payment_date'     => $reg->registration_date,
                    'payment_method'   => $method,
                    'status'           => 'paid',
                    'promotion_id'     => null,
                    'payment_confirmed'=> 1,
                    'note'             => 'Học phí lớp ' . $class->class_name,
                    'created_at'       => $reg->registration_date . ' 10:00:00',
                    'updated_at'       => now(),
                ];
            }
        }

        // Batch insert
        foreach (array_chunk($payments, 100) as $chunk) {
            DB::table('payments')->insert($chunk);
        }
    }
}
