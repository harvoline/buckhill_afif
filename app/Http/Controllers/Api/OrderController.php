<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Order;
use App\Models\JwtToken;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Lcobucci\JWT\Token\Parser;
use App\Http\Requests\OrderRequest;
use App\Http\Controllers\Controller;
use Lcobucci\JWT\Encoding\JoseEncoder;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/orders",
     *     tags={"Order"},
     *     summary="Get list of orders",
     *     description="Get list of orders. Make sure you authorize (using the top button) first before making request",
     *     operationId="listUserOrders",
     *     security={{"api_key_security":{*}}},
     *     @OA\Response(
     *         response="200",
     *         description="Successful logout",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                  example="string",
     *                  value={
     *                      {
     *                          "id": 24,
     *                          "uuid": "ca74882b-055d-3949-965f-d03f6ef1c3c6",
     *                          "user_id": 2,
     *                          "order_status_id": 3,
     *                          "payment_id": "3",
     *                          "products": "{}",
     *                          "address": "{}",
     *                          "delivery_fee": null,
     *                          "amount": 19059.14,
     *                          "created_at": "2023-04-07T05:37:51.000000Z",
     *                          "updated_at": "2023-04-07T05:37:51.000000Z",
     *                          "shipped_at": null
     *                      },
     *                      {
     *                          "id": 41,
     *                          "uuid": "60875ef5-b1bb-33f6-8fb9-31fde086f46e",
     *                          "user_id": 2,
     *                          "order_status_id": 3,
     *                          "payment_id": "8",
     *                          "products": "{}",
     *                          "address": "{}",
     *                          "delivery_fee": null,
     *                          "amount": 15811.2,
     *                          "created_at": "2023-04-07T05:37:51.000000Z",
     *                          "updated_at": "2023-04-07T05:37:51.000000Z",
     *                          "shipped_at": null
     *                      },
     *                      {
     *                          "id": 62,
     *                          "uuid": "e07515cd-addb-3eca-84dc-efdb88600674",
     *                          "user_id": 2,
     *                          "order_status_id": 4,
     *                          "payment_id": "2",
     *                          "products": "{}",
     *                          "address": "{}",
     *                          "delivery_fee": null,
     *                          "amount": 6.96,
     *                          "created_at": "2023-04-07T05:37:51.000000Z",
     *                          "updated_at": "2023-04-07T05:37:51.000000Z",
     *                          "shipped_at": null
     *                      },
     *                      {
     *                          "id": 72,
     *                          "uuid": "e3705a62-757a-3f73-ac67-324c80a3cbc9",
     *                          "user_id": 2,
     *                          "order_status_id": 1,
     *                          "payment_id": "4",
     *                          "products": "{}",
     *                          "address": "{}",
     *                          "delivery_fee": null,
     *                          "amount": 235.11,
     *                          "created_at": "2023-04-07T05:37:51.000000Z",
     *                          "updated_at": "2023-04-07T05:37:51.000000Z",
     *                          "shipped_at": null
     *                      },
     *                      {
     *                          "id": 84,
     *                          "uuid": "3ac936e0-e0bb-32d7-a2bb-02e3c007f607",
     *                          "user_id": 2,
     *                          "order_status_id": 4,
     *                          "payment_id": "3",
     *                          "products": "{}",
     *                          "address": "{}",
     *                          "delivery_fee": null,
     *                          "amount": 12,
     *                          "created_at": "2023-04-07T05:37:51.000000Z",
     *                          "updated_at": "2023-04-07T05:37:51.000000Z",
     *                          "shipped_at": null
     *                      },
     *                      {
     *                          "id": 90,
     *                          "uuid": "4d408068-2647-3863-afea-87b132d570b7",
     *                          "user_id": 2,
     *                          "order_status_id": 5,
     *                          "payment_id": "7",
     *                          "products": "{}",
     *                          "address": "{}",
     *                          "delivery_fee": null,
     *                          "amount": 8822.92,
     *                          "created_at": "2023-04-07T05:37:51.000000Z",
     *                          "updated_at": "2023-04-07T05:37:51.000000Z",
     *                          "shipped_at": null
     *                      },
     *                      {
     *                          "id": 97,
     *                          "uuid": "0dd72647-a3ee-3222-9038-9d26366d40d6",
     *                          "user_id": 2,
     *                          "order_status_id": 3,
     *                          "payment_id": "3",
     *                          "products": "{}",
     *                          "address": "{}",
     *                          "delivery_fee": null,
     *                          "amount": 287316.92,
     *                          "created_at": "2023-04-07T05:37:51.000000Z",
     *                          "updated_at": "2023-04-07T05:37:51.000000Z",
     *                          "shipped_at": null
     *                      }
     *                  },
     *                  summary="List of users"
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Invalid token",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid token"),
     *             @OA\Property(property="message", type="string", example="Error while decoding from Base64Url, invalid base64 characters detected"),
     *             @OA\Property(property="token", type="string", example="hgvhgv.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cDovL2xvY2FsaG9zdCIsImp0aSI6IjY0MmZiMDQwMjU2ZjciLCJpYXQiOjE2ODA4NDY5MTIuMTUzMjE5LCJleHAiOjE2ODA5MzMzMTIuMTUzMjE5LCJ1c2VyX2lkIjoxLCJ1c2VyX3V1aWQiOiJiMDJlNGFiZC1jNzliLTRkMWItYTI4Yi0zZWQ5N2QxMzlmNTUifQ.Yq1KDEr5R-WTYJ7803_Q1EmOxQg-C3ZRM_Nx9u7o-ec")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="User not found"),
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $token = $request->header('Authorization');
        $user = $this->getUserFromToken($token);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $orders = Order::where('user_id', $user->id)->get();
        return response()->json($orders);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/order/create",
     *     tags={"Order"},
     *     summary="Create new order",
     *     description="Create new order based on the data. Make sure you authorize (using the top button) first before making request",
     *     operationId="createUserOrders",
     *     security={{"api_key_security":{*}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="order_status_id", type="int", example=1),
     *             @OA\Property(property="products", type="string", example={}),
     *             @OA\Property(property="address", type="string", example={}),
     *             @OA\Property(property="amount", type="number", example=10.12),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful logout",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                  example="string",
     *                  value={
     *                      {
     *                          "id": 24,
     *                          "uuid": "ca74882b-055d-3949-965f-d03f6ef1c3c6",
     *                          "user_id": 2,
     *                          "order_status_id": 3,
     *                          "payment_id": "3",
     *                          "products": "{}",
     *                          "address": "{}",
     *                          "delivery_fee": null,
     *                          "amount": 19059.14,
     *                          "created_at": "2023-04-07T05:37:51.000000Z",
     *                          "updated_at": "2023-04-07T05:37:51.000000Z",
     *                          "shipped_at": null
     *                      }
     *                  },
     *                  summary="New order"
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Invalid token",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid token"),
     *             @OA\Property(property="message", type="string", example="Error while decoding from Base64Url, invalid base64 characters detected"),
     *             @OA\Property(property="token", type="string", example="hgvhgv.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cDovL2xvY2FsaG9zdCIsImp0aSI6IjY0MmZiMDQwMjU2ZjciLCJpYXQiOjE2ODA4NDY5MTIuMTUzMjE5LCJleHAiOjE2ODA5MzMzMTIuMTUzMjE5LCJ1c2VyX2lkIjoxLCJ1c2VyX3V1aWQiOiJiMDJlNGFiZC1jNzliLTRkMWItYTI4Yi0zZWQ5N2QxMzlmNTUifQ.Yq1KDEr5R-WTYJ7803_Q1EmOxQg-C3ZRM_Nx9u7o-ec")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="User not found"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Bad Request"),
     *             @OA\Property(property="message", type="string", example="..."),
     *         )
     *     )
     * )
     */
    public function store(CreateOrderRequest $request)
    {
        $token = $request->header('Authorization');
        $user = $this->getUserFromToken($token);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $newOrder = [
            'user_id' => $user->id,
            'uuid' => Str::uuid(),
            'order_status_id' => $request->input('order_status_id') ?? 1,
            'products' => json_encode($request->input('products')),
            'address' => json_encode($request->input('address')),
            'payment_id' => uniqid(),
            'amount' => $request->input('amount'),
            'delivery_fee' => $request->input('delivery_fee') ?? 0,
        ];

        try {
            $order = Order::create($newOrder);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Bad Request',
                'message' => $th->getMessage(),
            ], 400);
        }
        return response()->json($order, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/order/{uuid}",
     *     tags={"Order"},
     *     summary="Get order by uuid",
     *     description="Get order by uuid. Make sure you authorize (using the top button) first before making request",
     *     operationId="getUserOrder",
     *     security={{"api_key_security":{*}}},
     *     @OA\Parameter(
     *        name="uuid",
     *        in="path",
     *        description="Order UUID",
     *        required=true,
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful logout",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                  example="string",
     *                  value={
     *                      {
     *                          "id": 24,
     *                          "uuid": "ca74882b-055d-3949-965f-d03f6ef1c3c6",
     *                          "user_id": 2,
     *                          "order_status_id": 3,
     *                          "payment_id": "3",
     *                          "products": "{}",
     *                          "address": "{}",
     *                          "delivery_fee": null,
     *                          "amount": 19059.14,
     *                          "created_at": "2023-04-07T05:37:51.000000Z",
     *                          "updated_at": "2023-04-07T05:37:51.000000Z",
     *                          "shipped_at": null
     *                      },
     *                  },
     *                  summary="Show order"
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Invalid token",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid token"),
     *             @OA\Property(property="message", type="string", example="Error while decoding from Base64Url, invalid base64 characters detected"),
     *             @OA\Property(property="token", type="string", example="hgvhgv.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cDovL2xvY2FsaG9zdCIsImp0aSI6IjY0MmZiMDQwMjU2ZjciLCJpYXQiOjE2ODA4NDY5MTIuMTUzMjE5LCJleHAiOjE2ODA5MzMzMTIuMTUzMjE5LCJ1c2VyX2lkIjoxLCJ1c2VyX3V1aWQiOiJiMDJlNGFiZC1jNzliLTRkMWItYTI4Yi0zZWQ5N2QxMzlmNTUifQ.Yq1KDEr5R-WTYJ7803_Q1EmOxQg-C3ZRM_Nx9u7o-ec")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Order not found"),
     *         )
     *     )
     * )
     */
    public function show(Request $request, $uuid)
    {
        $token = $request->header('Authorization');
        $user = $this->getUserFromToken($token);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $order = Order::where('user_id', $user->id)
            ->where('uuid', $uuid)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/order/{uuid}",
     *     tags={"Order"},
     *     summary="Update order by uuid",
     *     description="Update order by uuid. Make sure you authorize (using the top button) first before making request",
     *     operationId="updateUserOrder",
     *     security={{"api_key_security":{*}}},
     *     @OA\Parameter(
     *        name="uuid",
     *        in="path",
     *        description="Order UUID",
     *        required=true,
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="order_status_id", type="int", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful logout",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                  example="string",
     *                  value={
     *                      {
     *                          "id": 24,
     *                          "uuid": "ca74882b-055d-3949-965f-d03f6ef1c3c6",
     *                          "user_id": 2,
     *                          "order_status_id": 3,
     *                          "payment_id": "3",
     *                          "products": "{}",
     *                          "address": "{}",
     *                          "delivery_fee": null,
     *                          "amount": 19059.14,
     *                          "created_at": "2023-04-07T05:37:51.000000Z",
     *                          "updated_at": "2023-04-07T05:37:51.000000Z",
     *                          "shipped_at": null
     *                      },
     *                  },
     *                  summary="Updated order"
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Invalid token",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid token"),
     *             @OA\Property(property="message", type="string", example="Error while decoding from Base64Url, invalid base64 characters detected"),
     *             @OA\Property(property="token", type="string", example="hgvhgv.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cDovL2xvY2FsaG9zdCIsImp0aSI6IjY0MmZiMDQwMjU2ZjciLCJpYXQiOjE2ODA4NDY5MTIuMTUzMjE5LCJleHAiOjE2ODA5MzMzMTIuMTUzMjE5LCJ1c2VyX2lkIjoxLCJ1c2VyX3V1aWQiOiJiMDJlNGFiZC1jNzliLTRkMWItYTI4Yi0zZWQ5N2QxMzlmNTUifQ.Yq1KDEr5R-WTYJ7803_Q1EmOxQg-C3ZRM_Nx9u7o-ec")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Order not found"),
     *         )
     *     )
     * )
     */
    public function update(UpdateOrderRequest $request, $uuid)
    {
        $token = $request->header('Authorization');
        $user = $this->getUserFromToken($token);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $order = Order::where('user_id', $user->id)
            ->where('uuid', $uuid)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->fill(array_merge($request->all(), [
            'user_id' => $user->id,
            'uuid' => $uuid
        ]));
        $order->save();
        return response()->json($order);
    }

    public function destroy(Request $request, $uuid)
    {
        $token = $request->header('Authorization');
        $user = $this->getUserFromToken($token);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $order = Order::where('user_id', $user->id)
            ->where('uuid', $uuid)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        Order::find($order->id)->delete();
        return response()->json(['message' => 'Order deleted']);
    }

    protected function getUserFromToken($token)
    {
        $parser = new Parser(new JoseEncoder());
        $parsedToken = $parser->parse($token);

        $user_token = JwtToken::where('unique_id', $parsedToken->claims()->get('user_uuid'))
            ->where('user_id', $parsedToken->claims()->get('user_id'))
            ->first();

        if (!$user_token) {
            return false;
        }

        $user = User::where('id', $parsedToken->claims()->get('user_id'))->first();

        if (!$user) {
            return false;
        }

        return $user;
    }
}
