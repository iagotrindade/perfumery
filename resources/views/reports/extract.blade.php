<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Extrato da Venda #{{ $sale->id }}</title>
    <style type="text/css">
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            color: #333;
            background-color: white;
            padding: 25px;
            line-height: 1.5;
            font-size: 14px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #fbbf24;
            padding-bottom: 20px;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            color: #78350f;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .section {
            margin-bottom: 15px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #78350f;
            border-left: 5px solid #fbbf24;
            padding-left: 10px;
            margin-bottom: 10px;
        }

        .info-table td {
            padding: 4px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .table th {
            background-color: #78350f;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 13px;
        }

        .table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }

        .table tr:nth-child(even) {
            background-color: #fefce8;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #999;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1 class="title">Extrato de Venda</h1>
        <p class="subtitle"><strong>Flávio e Diana Perfumaria</strong></p>
        <p class="subtitle">Emitido em {{ $reportDate }}</p>
    </div>

    <div class="section">
        <h2 class="section-title">Informações do Cliente</h2>
        <table class="info-table">
            <tr>
                <td><strong>Nome:</strong></td>
                <td>{{ $sale->customer->name }}</td>
            </tr>
            <tr>
                <td><strong>CPF:</strong></td>
                <td>{{ $sale->customer->cpf ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title">Informações da Venda</h2>
        <table class="info-table">
            <tr>
                <td><strong>ID da Venda:</strong></td>
                <td>#{{ $sale->id }}</td>
            </tr>
            <tr>
                <td><strong>Data da Venda:</strong></td>
                <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td>{{ ucfirst($sale->hasPendingOrOverdueInstallments ? 'Pendente' : 'Pago') }}</td>
            </tr>
            <tr>
                <td><strong>Forma de Pagamento:</strong></td>
                <td>{{ ucfirst($sale->payment_method) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title">Informações de Pagamento e Parcelamento</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>Parcela</th>
                    <th>Valor</th>
                    <th>Vencimento</th>
                    <th>Pagamento</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->installments as $installment)
                    <tr>
                        <td>{{ $installment->installment_number }}</td>
                        <td>R$ {{ number_format($installment->amount, 2, ',', '.') }}</td>
                        <td>{{ Carbon\Carbon::parse($installment->due_date)->format('d/m/Y') }}</td>
                        <td>
                            {{ $installment->payment_date ? \Carbon\Carbon::parse($installment->payment_date)->format('d/m/Y') : '-' }}
                        </td>
                        <td>{{ $installment->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title">Produtos</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->products as $product)
                    <tr>
                        <td>{{ $product->product->name }}</td>
                        <td>{{ $product->quantity }}</td>
                        <td>R$ {{ number_format($product->product->sale_value, 2, ',', '.') }}</td>
                        <td>
                            R$ {{ number_format($product->product->sale_value * $product->quantity, 2, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title">Totais</h2>
        <table class="info-table">
            @if ($sale->discount > 0)
                <tr>
                    <td><strong>Total Bruto:</strong></td>
                    <td><strong>R$ {{ number_format($sale->raw_total, 2, ',', '.') }}</strong></td>
                </tr>
            @endif
            @if ($sale->discount > 0)
                <tr>
                    <td><strong>Desconto:</strong></td>
                    <td>R$ {{ number_format($sale->discount, 2, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td><strong>Total Final:</strong></td>
                <td><strong>R$ {{ number_format($sale->total, 2, ',', '.') }}</strong></td>
            </tr>

            <tr>
                <td><strong>Pago até o momento:</strong></td>
                <td><strong>R$ {{ number_format($sale->paid, 2, ',', '.') }}</strong></td>
            </tr>

            <tr>
                <td><strong>Valor em aberto:</strong></td>
                <td><strong>R$ {{ number_format($sale->debt, 2, ',', '.') }}</strong></td>
            </tr>

        </table>
    </div>

    @if ($sale->observations)
        <div class="section">
            <h2 class="section-title">Observações</h2>
            <p>{{ $sale->observations }}</p>
        </div>
    @endif

    <div class="footer">
        Extrato gerado em {{ $reportDate }} | Flávio e Diana Perfumaria
    </div>
</body>

</html>
