<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\BankTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $account = $request->user()->accounts()->with('user')->findOrFail($request->integer('account_id', $request->user()->accounts()->value('id')));
        $transactions = $account->transactions()->latest()->paginate(10)->withQueryString();
        $accounts = $request->user()->accounts()->orderBy('account_number')->get();

        return view('banking.dashboard', compact('account', 'transactions', 'accounts'));
    }

    public function create(Request $request): View
    {
        return view('banking.create', [
            'accounts' => $request->user()->accounts()->orderBy('account_number')->get(),
            'destinations' => Account::whereNotIn('id', $request->user()->accounts()->pluck('id'))->orderBy('account_number')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'type' => ['required', Rule::in(['deposit', 'withdrawal', 'transfer'])],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:100000000'],
            'to_account_id' => ['nullable', 'required_if:type,transfer', 'integer', 'exists:accounts,id', 'different:account_id'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $account = DB::transaction(function () use ($data): Account {
            $source = Account::query()->where('user_id', auth()->id())->lockForUpdate()->findOrFail($data['account_id']);
            $amount = (float) $data['amount'];

            if (in_array($data['type'], ['withdrawal', 'transfer'], true) && (float) $source->balance < $amount) {
                abort(422, 'Insufficient funds for this transaction.');
            }

            $reference = 'TXN-' . strtoupper(bin2hex(random_bytes(5)));
            $description = $data['description'] ?? ucfirst($data['type']);

            if ($data['type'] === 'transfer') {
                $destination = Account::query()->lockForUpdate()->findOrFail($data['to_account_id']);
                BankTransaction::create([
                    'account_id' => $source->id,
                    'type' => 'transfer_out',
                    'amount' => $amount,
                    'reference' => $reference,
                    'description' => $description . ' to ' . $destination->account_number,
                    'related_account_id' => $destination->id,
                    'balance_after' => 0,
                ]);
                BankTransaction::create([
                    'account_id' => $destination->id,
                    'type' => 'transfer_in',
                    'amount' => $amount,
                    'reference' => $reference,
                    'description' => $description . ' from ' . $source->account_number,
                    'related_account_id' => $source->id,
                    'balance_after' => 0,
                ]);
            } else {
                BankTransaction::create([
                    'account_id' => $source->id,
                    'type' => $data['type'],
                    'amount' => $amount,
                    'reference' => $reference,
                    'description' => $description,
                    'balance_after' => 0,
                ]);
            }

            return $source->fresh();
        });

        return redirect()->route('dashboard', ['account_id' => $account->id])
            ->with('success', 'Transaction completed successfully.');
    }
}
