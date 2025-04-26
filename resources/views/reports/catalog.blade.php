<!DOCTYPE html>
<html lang="pr_BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <title>Catalogo Flávio e Diana Perfumaria</title>
</head>

<body>
    <header class="flex items-center justify-between px-6 md:px-20 py-6" style="background-color: #fbbf24;">
        <!-- Logo + Subtítulo -->
        <div>
            <h1 class="text-4xl font-serif text-brown-800" style="color: #FFFFFF; font-weight:700;">Flávio e Diana Perfumaria</h1>
            <p class="text-sm text-gray-600" style="color: #FFFFFA;">Descubra nossos produtos encantadores e de alta qualidade</p>
        </div>

        <!-- Ícone da sacola -->
        <button class="w-10 h-10 flex items-center justify-center rounded-full bg-brown-800 hover:bg-brown-700 transition">
            <!-- Ícone (usando um emoji como exemplo) -->
            <span class="text-white text-xl" style="color: #FFFFFF;">🛍️</span>
        </button>
    </header>

    <div class="bg-[#f5f1ea] min-h-screen flex flex-col">
        @foreach ($brands as $brand)
            <!-- Cabeçalho -->
            <div class="text-center py-10">
                <h1 class="text-4xl font-serif text-brown-800"> {{ $brand->name }} </h1>
            </div>

            <!-- Produtos -->
            <section class="grid grid-cols-2 md:grid-cols-3 gap-8 px-6 md:px-20">
                <!-- Produto -->

                @foreach ($brand->products as $product)
                    @if ($product->show_on_catalog)

                        <div class="flex flex-col items-center">
                            @if($product->image)
                                <img src="{{ url('storage/'.$product->image.'') }}" alt="Imagem do Produto" class="rounded-lg mb-2">
                            @else
                                <img src="imagem1.jpg" alt="Anel Poderosa" class="rounded-lg mb-2">
                            @endif
                            <h2 class="text-lg font-medium text-gray-800"> {{ $product->name }} </h2>
                            <p class="text-brown-600 font-bold">R${{ $product->sale_value }}</p>
                        </div>

                    @endif

                @endforeach

            </section>

        @endforeach

        <!-- Rodapé -->
        <footer class="bg-brown-800 text-white px-6 md:px-20 py-10 mt-16" style="background-color: #fbbf24;">
            <div class="flex flex-col md:flex-row md:justify-around md:items-start gap-10">

                <!-- Informações da Loja -->
                <div class="flex  flex-col justify-center items-center">
                    <h3 class="text-2xl font-serif mb-2">Flávio e Diana Perfumaria</h3>
                    <p class="text-sm text-gray-300" style="color: #FFFFFA;">
                        Entre em contato agora pelo telefone (00) 12345-6789.
                    </p>
                </div>
            </div>

            <!-- Linha fina separadora -->
            <div class="border-t border-brown-700 my-8"></div>

            <!-- Direitos -->
            <div class="text-center text-xs text-gray-400" style="color: #FFFFFF;">
                © 2025 Lira e Cia - Todos os direitos reservados.
            </div>
        </footer>

    </div>
</body>

</html>