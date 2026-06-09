<?php

namespace App\Services;

use App\Repositories\InventoryRepository;
use App\Repositories\ItemRepository;
use App\Repositories\PromotionRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;

class ItemService
{
    private $itemRepository;
    private $promotionRepository;
    private $userRepository;
    private $inventoryRepository;
    private $transactionRepository;

    public function __construct(
        ItemRepository $itemRepository,
        PromotionRepository $promotionRepository,
        UserRepository $userRepository,
        InventoryRepository $inventoryRepository,
        TransactionRepository $transactionRepository
    ) {
        $this->itemRepository = $itemRepository;
        $this->promotionRepository = $promotionRepository;
        $this->userRepository = $userRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function getAll()
    {
        $list = $this->itemRepository->all();

        return $list;
    }

    public function getDetail($id)
    {
        $record = $this->itemRepository->first([
            'id' => $id
        ]);

        return $record;
    }

    public function getPromotion()
    {
        $list = $this->promotionRepository->all();

        return $list;
    }

    public function purchase($request)
    {
        $userId = $request['user_id'];
        $itemId = $request['item_id'];
        $user = $this->userRepository->first(['id' => $userId]);
        $item = $this->itemRepository->first(['id' => $itemId]);

        if (empty($item)) {
            return ['success' => false, 'message' => 'Không tồn tại vật phẩm'];
        }

        $percent = 0;
        $dateCurrent = date('Y-m-d');
        $promotion = $this->promotionRepository->get(['item_id' => $itemId]);
        foreach ($promotion as $promo) {
            if (strtotime($promo->start_date) >= strtotime($dateCurrent) && strtotime($dateCurrent) <= strtotime($promo->end_date)) {
                $percent = $promo->discount_percent;
            }
        }
        $price = $item->price;
        if ($percent > 0) {
            $price = $item->price - ($percent * $item->price) / 100;
        }

        if ($price <= 0) {
            return ['success' => false, 'message' => 'Giá không hợp lệ'];
        }

        $transaction = [
            'user_id' => $userId,
            'item_id' => $itemId,
            'transaction_date' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'amount' => $item->price,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($user->balance >= $price) {
            $userUpdate = [
                'balance' => $user->balance - $price,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $transaction['status'] = 'completed';
            $inventory = [
                'user_id' => $userId,
                'item_id' => $itemId,
                'quantity' => 1,
                'date_purchased' => date('Y-m-d H:i:s'),
                'expiration_date' => $item->end_date,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $this->transactionRepository->create($transaction);
            $this->inventoryRepository->create($inventory);
            $this->userRepository->updateByFilters([
                'id' => $userId
            ], $userUpdate);
        } else {
            return ['success' => false, 'message' => 'Số tiền của bạn không đủ'];
        }

        return ['success' => true, 'message' => 'Cập nhật thành công'];
    }

    public function getListInventory($userId)
    {
        $list = $this->inventoryRepository->get(['user_id' => $userId]);

        return $list;
    }

    public function getListTransaction($userId)
    {
        $list = $this->transactionRepository->get([
            'user_id' => $userId
        ]);

        return $list;
    }
}
