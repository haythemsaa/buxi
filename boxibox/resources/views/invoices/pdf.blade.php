<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .invoice-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 18px;
            font-weight: bold;
            color: #4F46E5;
        }
        .clear {
            clear: both;
        }
        .customer-section {
            margin: 30px 0;
            padding: 15px;
            background-color: #f9fafb;
            border-left: 4px solid #4F46E5;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #4F46E5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background-color: #4F46E5;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            margin-top: 30px;
            float: right;
            width: 40%;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        .grand-total {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #4F46E5;
            padding-top: 10px;
            margin-top: 10px;
        }
        .payment-info {
            margin-top: 50px;
            clear: both;
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 5px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-overdue {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <div class="company-name">{{ config('app.name', 'Boxibox') }}</div>
            <div>{{ $tenant->name ?? 'Nom du locataire' }}</div>
            <div>{{ $tenant->address ?? 'Adresse' }}</div>
            <div>{{ $tenant->postal_code ?? '' }} {{ $tenant->city ?? '' }}</div>
            @if(isset($tenant->phone))
                <div>Tél: {{ $tenant->phone }}</div>
            @endif
            @if(isset($tenant->email))
                <div>Email: {{ $tenant->email }}</div>
            @endif
        </div>

        <div class="invoice-info">
            <div class="invoice-number">FACTURE</div>
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            <div style="margin-top: 15px;">
                <strong>Date:</strong> {{ $invoice->issue_date->format('d/m/Y') }}<br>
                <strong>Échéance:</strong> {{ $invoice->due_date->format('d/m/Y') }}<br>
                <span class="status-badge status-{{ $invoice->status }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="customer-section">
        <div class="section-title">CLIENT</div>
        <div>
            <strong>{{ $invoice->contract->customer->first_name }} {{ $invoice->contract->customer->last_name }}</strong><br>
            {{ $invoice->contract->customer->email }}<br>
            @if($invoice->contract->customer->phone)
                Tél: {{ $invoice->contract->customer->phone }}<br>
            @endif
            @if($invoice->contract->customer->address)
                {{ $invoice->contract->customer->address }}<br>
                {{ $invoice->contract->customer->postal_code }} {{ $invoice->contract->customer->city }}
            @endif
        </div>
    </div>

    <div class="section-title">DÉTAILS DU CONTRAT</div>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Site</th>
                <th>Box</th>
                <th class="text-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Contrat {{ $invoice->contract->contract_number }}</strong><br>
                    Période: {{ $invoice->period_start->format('d/m/Y') }} au {{ $invoice->period_end->format('d/m/Y') }}
                </td>
                <td>{{ $invoice->contract->box->floor->building->site->name }}</td>
                <td>{{ $invoice->contract->box->box_number }}</td>
                <td class="text-right">{{ number_format($invoice->amount, 2, ',', ' ') }} €</td>
            </tr>
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span>Sous-total HT:</span>
            <span>{{ number_format($invoice->amount, 2, ',', ' ') }} €</span>
        </div>
        <div class="total-row">
            <span>TVA ({{ $invoice->tax_rate }}%):</span>
            <span>{{ number_format($invoice->tax_amount, 2, ',', ' ') }} €</span>
        </div>
        <div class="total-row grand-total">
            <span>Total TTC:</span>
            <span>{{ number_format($invoice->total_amount, 2, ',', ' ') }} €</span>
        </div>
    </div>

    <div class="clear"></div>

    @if($invoice->payments->count() > 0)
        <div class="payment-info">
            <div class="section-title">PAIEMENTS REÇUS</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Méthode</th>
                        <th>Référence</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                            <td>{{ ucfirst($payment->payment_method) }}</td>
                            <td>{{ $payment->transaction_reference ?? '-' }}</td>
                            <td class="text-right">{{ number_format($payment->amount, 2, ',', ' ') }} €</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="text-align: right; margin-top: 10px;">
                <strong>Total payé: {{ number_format($invoice->payments->sum('amount'), 2, ',', ' ') }} €</strong>
            </div>
        </div>
    @endif

    @if($invoice->notes)
        <div style="margin-top: 30px;">
            <div class="section-title">NOTES</div>
            <p>{{ $invoice->notes }}</p>
        </div>
    @endif

    <div class="footer">
        <p>Merci de votre confiance !</p>
        <p>Ce document est une facture générée automatiquement.</p>
        <p>En cas de question, contactez-nous à {{ $tenant->email ?? config('mail.from.address') }}</p>
    </div>
</body>
</html>
