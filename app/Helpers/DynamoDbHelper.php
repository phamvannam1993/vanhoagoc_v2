<?php

namespace App\Helpers;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Exception;
use Illuminate\Support\Str;

class DynamoDbHelper
{
    public static function getClient()
    {
        return new DynamoDbClient([
            'region' => config('services.dynamodb.region'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('services.dynamodb.key'),
                'secret' => config('services.dynamodb.secret')
            ]
        ]);
    }

    public static function getItem($conditions)
    {
        $client = self::getClient();

        $result = $client->query($conditions);

        return !empty($result['Items'][0]) ? $result['Items'][0] : false;
    }

    public static function putItem($table, $item)
    {
        try {
            $marshaler = new Marshaler();

            $params = [
                'TableName' => $table,
                'Item' => $marshaler->marshalItem($item),
                'ConditionExpression' => 'attribute_not_exists(login_name)',
            ];

            $client = self::getClient();

            $client->putItem($params);

            return true;
        } catch (Exception $e) {
            Helper::logException($e);
            return false;
        }
    }

    public static function deleteItem($params)
    {
        try {
            $client = self::getClient();

            $client->deleteItem($params);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function updateUserInfo($userId, $data)
    {
        $expressionParts = [];
        $attributeNames = [];
        $attributeValues = [];
        $uuid = Str::uuid()->toString();

        if (!empty($data['password'])) {
            $expressionParts[] = '#pwd = :password';
            $attributeNames['#pwd'] = 'password';
            $attributeValues[':password'] = ['S' => sha1($data['password'] . $uuid)];

            $expressionParts[] = '#hash = :hash';
            $attributeNames['#hash'] = 'hash';
            $attributeValues[':hash'] = ['S' => $uuid];
        }

        if (!empty($data['phone_number'])) {
            $expressionParts[] = '#phone = :phone';
            $attributeNames['#phone'] = 'phone_number';
            $attributeValues[':phone'] = ['S' => $data['phone_number']];
        }

        if (!empty($data['full_name'])) {
            $expressionParts[] = '#name = :name';
            $attributeNames['#name'] = 'full_name';
            $attributeValues[':name'] = ['S' => $data['full_name']];
        }

        if (empty($expressionParts)) {
            return false;
        }

        $update = [
            'TableName' => 'cst.users',
            'Key' => [
                'user_id' => ['S' => $userId],
            ],
            'UpdateExpression' => 'SET ' . implode(', ', $expressionParts),
            'ExpressionAttributeNames' => $attributeNames,
            'ExpressionAttributeValues' => $attributeValues,
        ];

        return self::getClient()->updateItem($update);
    }
}
