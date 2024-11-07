<?php
session_start(); // Iniciar sessão aqui, no início do script
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/fone1.css">
    <link rel="stylesheet" href="css/style.css">
    <title>Infinity Tech</title>
    <style>
        .search-bar-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            margin-bottom: 20px;
            position: relative;
            width: 60%;
            margin: 0 auto;
        }

        .search-bar {
            width: 100%;
            padding: 10px 20px 10px 40px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .search-bar::placeholder {
            color: #ccc;
        }

        .search-icon {
            position: absolute;
            left: 10px;
            margin-top: 125px;
            font-size: 20px;
            color: #1d7a7f;
        }
    </style>
</head>

<body>
    <header>
        <nav class="navigation">
            <a href="#" class="logo">Infi<span>ni</span>ty<span>te</span>ch</a>
            <div class="nav">
                <a href="cart.php" style="color:black;" onclick="toggleCart()"><i class='bx bx-cart-alt'>
                        Carrinho</i></a>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="#">Inicio</a></li>
                <li class="nav-item"><a href="products.php">Produtos</a></li>
                <li class="nav-item"><a href="help.html">Ajuda</a></li>
                <li class="nav-item">
                    <?php if (isset($_SESSION['administrador']) && $_SESSION['administrador'] == 'yes'): ?>
                        <a href="admin_dashboard.php">Admin Dashboard</a>
                    <?php else: ?>
                        <a href="perfil.php">Perfil</a>
                    <?php endif; ?>
                </li>
            </ul>
            <div class="menu" onclick="toggleMenu()">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </nav>
        <br>
        <br>
        <!-- Barra de pesquisa -->
    </header>

    <!-- Resto do código HTML -->

    <main>
        <section class="home">
            <div class="home-text">
                <h1 class="text-h1" id="dynamic-text">
                    <?php
                    if (isset($_SESSION['full_name']) && (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] == 0)) {
                        echo "Olá, " . explode(" ", htmlspecialchars($_SESSION['full_name']))[0] . "!";
                    } else {
                        echo "Olá, Usuário!";
                    }
                    ?>
                </h1>

                <br><br><br>
                <a href="products.php" class="home-btn">Navegar</a>
            </div>
        </section>
        <div class="servicos">
            <div class="servicos-text">
                <h2 class="text-h2">Nossos produtos destque</h2>
                <div class="filter-controls">
                    <div class="price-filter">
                     <a href="products.php"><p>ver mais produtos</p></a>
                    
                        <label for="price-range">Filtrar por preço até: R$<span id="price-value">1000</span></label>
                        <input type="range" id="price-range" min="0" max="1000" step="10" value="1000">
                    </div>
                    <div class="category-filter">
                        <button class="filter-btn" data-category="all">Todos</button>
                        <button class="filter-btn" data-category="headset">Headset</button>
                        <button class="filter-btn" data-category="microfone">Microfone</button>
                        <button class="filter-btn" data-category="monitor">Monitor</button>
                        <button class="filter-btn" data-category="mousepad">Mousepad</button>
                    </div>
                </div>
            </div>
            <div class="servicos-grid">
               
                    <div class="servico-item" data-category="headset" data-price="349">
                        <img src="https://encrypted-tbn0.gstatic.com/shopping?q=tbn:ANd9GcQf3O0BM7ZyD3iWLTQTI-ylkJdO0Ek5d6PemQDNWmiob8tcHhWQKJq6outxoFQC21YCuuq2lCMJrYkPUOnHIQebvpNqpYjLSO8m3m4HuO8DwikvdklEc3vj-g&usqp=CAE"
                            alt="Headset 1">
                        <div class="servico-info">
                            <h3 class="servico-title">Headset Gamer HyperX Cloud Stinger</h3>
                            <p class="servico-description"></p>
                        </div>
        
            </div>
            <div class="servico-item" data-category="headset" data-price="499">
                
                    <img src="https://encrypted-tbn2.gstatic.com/shopping?q=tbn:ANd9GcSLYDZ8xOZZcjVz6nX_LZojOVvZWO-ZV-QUNojR24GJu5m8vDhrvzMdL7AloVj-sQqjlHdgkeZW54fWUnVC9i2A8ikVw5AHBlKPylVNXYtO4BZTZ5Lg60pG6A&usqp=CAE"
                        alt="">
                    <div class="servico-info">
                        <h3 class="servico-title">Headset Logitech G533 Wireless</h3>
                        <p class="servico-description"></p>
    
            </div>
        </div>
        <div class="servico-item" data-category="microfone" data-price="399">
            
            <img src="https://encrypted-tbn1.gstatic.com/shopping?q=tbn:ANd9GcQ0NbL_68W1RfCtFJYzoY6CwGMBq_ohpKkT6iL5w-deBqbI0B-YA0ITlTdh4I2npFwcCrR9d0he8_6Bl4fyOWYTlaxZWr5CClTJCRSVxhaLOvv6OsGpVjQ4&usqp=CAE"
                alt="Headset 3">
            <div class="servico-info">
                <h3 class="servico-title">Microfone HyperX</h3>
                <p class="servico-description"></p>
                
            </div>
        </div>
        <div class="servico-item" data-category="microfone" data-price="799">
            <img src="https://encrypted-tbn1.gstatic.com/shopping?q=tbn:ANd9GcQ0tDaObZRkvvx0CQvcmvo2L_FXYkKQx80GVcKmaL0N83YQoJLYsHPHWtCCleEAOQwv8UnQeoTU1jNO8BwrylOsml9yIjN3G2G8QikFL0yGI51EadOOBvmPcA&usqp=CAE"
                alt="Microfone 1">
            <div class="servico-info">
                <h3 class="servico-title">Microfone Blue Yeti USB</h3>
                <p class="servico-description"></p>
            </div>
        </div>
        <div class="servico-item" data-category="monitor" data-price="899">
            <img src="https://encrypted-tbn3.gstatic.com/shopping?q=tbn:ANd9GcTPUlZx5Q_p9YaUNMtrLFp_8oRT_8jaKSxojxbCBOhRyxMYRxP0Q0VJ_av4bgNzRJpIEyuX1ZgBUZF6jTY8NuF43pJ6enUZRZagQcHIHUdVjUPOggck8TdLXA&usqp=CAE"
                alt="Microfone 2">
            <div class="servico-info">
                <h3 class="servico-title">
                    Monitor Concórdia Gamer Curvo</h3>
                <p class="servico-description"></p>
            </div>
        </div>
        <div class="servico-item" data-category="monitor" data-price="129">
            <img src="https://encrypted-tbn0.gstatic.com/shopping?q=tbn:ANd9GcSz90rGS2TpmYC7UCripQgPxLc8E8E5If34j1cI_ryN2yhmfN8Z6dxPel4mIxEZesteBG0DPRUkiJ7-2bOtiDot94fANQ7A6ooe0xMJ5zfmunRJ2GosOlJa&usqp=CAE"
                alt="Mousepad 1">
            <div class="servico-info">
                <h3 class="servico-title">Monitor Gamer Acer</h3>
                <p class="servico-description"></p>
            </div>
        </div>
        <div class="servico-item" data-category="mousepad" data-price="149">
            <img src="https://encrypted-tbn0.gstatic.com/shopping?q=tbn:ANd9GcRjk27FLPv-S7Wb8Y29mKM2fuamIS2P7DaAWZLHlWiJktGJial3lG2bfMxKZLi3_nXQjJOAAtodpCHwCHYbwRoDiCbZUgfSZcA1v7NpaugQy_Yxe5WsDGAPoXJmkE-jiH1Oaherlhk&usqp=CAc"
                alt="Mousepad 2">
            <div class="servico-info">
                <h3 class="servico-title">Mousepad PcYes!</h3>
                <p class="servico-description"></p>
            </div>
        </div>
        <div class="servico-item" data-category="mousepad" data-price="149">
            <img src="https://encrypted-tbn1.gstatic.com/shopping?q=tbn:ANd9GcTx9iQbcvevCpwHbDXM7TbeuwZQ40_FjSAP8l0nGR4uip6zhYIDfgyTYVIa5aTS6a-_lxYkJhyr_8wHmvEO1GPeD0ARrEhZSrs9gPhnfOFi&usqp=CAE"
                alt="Mousepad 2">
            <div class="servico-info">
                <h3 class="servico-title">Mousepad HyperX Fury S</h3>
                <p class="servico-description"></p>
            </div>
        </div>
        </div>
        </div>

        <section class="quem-somos">
            <div class="quem-somos-text">
                <h2 class="text-h2">Quem Somos</h2>
                <p>Bem-vindo à nossa loja de periféricos de alta qualidade! Oferecemos uma ampla gama de acessórios e
                    dispositivos, desde teclados mecânicos avançados até mouses ergonômicos e headsets imersivos. Nossa
                    seleção inclui produtos das melhores marcas, garantindo durabilidade, desempenho e estilo.
                    Atualizados com as últimas tendências, temos a solução perfeita para otimizar seu setup, melhorar a
                    produtividade, maximizar a precisão nos jogos ou elevar seu entretenimento. Compre agora e
                    transforme sua experiência digital!</p>
            </div>
            <div class="team">
                <h2 class="text-h2 text-center text-2xl mb-6">Nossa Equipe</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        class="group before:hover:scale-95 before:hover:h-72 before:hover:w-80 before:hover:h-44 before:hover:rounded-b-2xl before:transition-all before:duration-500 before:content-[''] before:w-80 before:h-24 before:rounded-t-2xl before:bg-gradient-to-bl from-sky-200 via-orange-200 to-orange-700 before:absolute before:top-0 w-80 h-72 relative bg-slate-50 flex flex-col items-center justify-center gap-2 text-center rounded-2xl overflow-hidden">
                        <div
                            class="w-28 h-28 rounded-full border-4 border-slate-50 z-10 group-hover:scale-150 group-hover:-translate-x-24 group-hover:-translate-y-20 transition-all duration-500 overflow-hidden">
                            <img src="img/Ana.jpg" alt="Profile Image" class="w-full h-full object-cover">
                        </div>
                        <div class="z-10 group-hover:-translate-y-10 transition-all duration-500">
                            <span class="text-2xl font-semibold">Ana Clara Engles Rottini</span>
                            <p>Documentação</p>
                        </div>
                    </div>
                    <div
                        class="group before:hover:scale-95 before:hover:h-72 before:hover:w-80 before:hover:h-44 before:hover:rounded-b-2xl before:transition-all before:duration-500 before:content-[''] before:w-80 before:h-24 before:rounded-t-2xl before:bg-gradient-to-bl from-sky-200 via-orange-200 to-orange-700 before:absolute before:top-0 w-80 h-72 relative bg-slate-50 flex flex-col items-center justify-center gap-2 text-center rounded-2xl overflow-hidden">
                        <div
                            class="w-28 h-28 rounded-full border-4 border-slate-50 z-10 group-hover:scale-150 group-hover:-translate-x-24 group-hover:-translate-y-20 transition-all duration-500 overflow-hidden">
                            <img src="img/arthur.jpg" alt="Profile Image" class="w-full h-full object-cover">
                        </div>
                        <div class="z-10 group-hover:-translate-y-10 transition-all duration-500">
                            <span class="text-2xl font-semibold">Arthur Henrique Pereira</span>
                            <p>Documentação</p>
                        </div>
                    </div>
                    <div
                        class="group before:hover:scale-95 before:hover:h-72 before:hover:w-80 before:hover:h-44 before:hover:rounded-b-2xl before:transition-all before:duration-500 before:content-[''] before:w-80 before:h-24 before:rounded-t-2xl before:bg-gradient-to-bl from-sky-200 via-orange-200 to-orange-700 before:absolute before:top-0 w-80 h-72 relative bg-slate-50 flex flex-col items-center justify-center gap-2 text-center rounded-2xl overflow-hidden">
                        <div
                            class="w-28 h-28 rounded-full border-4 border-slate-50 z-10 group-hover:scale-150 group-hover:-translate-x-24 group-hover:-translate-y-20 transition-all duration-500 overflow-hidden">
                            <img src="img/pacheco.png" alt="Profile Image" class="w-full h-full object-cover">
                        </div>
                        <div class="z-10 group-hover:-translate-y-10 transition-all duration-500">
                            <span class="text-2xl font-semibold">Lucas Silva Pacheco</span>
                            <p>Documentação</p>
                        </div>
                    </div>
                    <div
                        class="group before:hover:scale-95 before:hover:h-72 before:hover:w-80 before:hover:h-44 before:hover:rounded-b-2xl before:transition-all before:duration-500 before:content-[''] before:w-80 before:h-24 before:rounded-t-2xl before:bg-gradient-to-bl from-sky-200 via-orange-200 to-orange-700 before:absolute before:top-0 w-80 h-72 relative bg-slate-50 flex flex-col items-center justify-center gap-2 text-center rounded-2xl overflow-hidden">
                        <div
                            class="w-28 h-28 rounded-full border-4 border-slate-50 z-10 group-hover:scale-150 group-hover:-translate-x-24 group-hover:-translate-y-20 transition-all duration-500 overflow-hidden">
                            <img src="img/emanuel.jpg" alt="Profile Image" class="w-full h-full object-cover">
                        </div>
                        <div class="z-10 group-hover:-translate-y-10 transition-all duration-500">
                            <span class="text-2xl font-semibold">Emanuel Tonis Florz Filho</span>
                            <p>Desenvolverdor Front/Back-end</p>
                        </div>
                    </div>
                    <div
                        class="group before:hover:scale-95 before:hover:h-72 before:hover:w-80 before:hover:h-44 before:hover:rounded-b-2xl before:transition-all before:duration-500 before:content-[''] before:w-80 before:h-24 before:rounded-t-2xl before:bg-gradient-to-bl from-sky-200 via-orange-200 to-orange-700 before:absolute before:top-0 w-80 h-72 relative bg-slate-50 flex flex-col items-center justify-center gap-2 text-center rounded-2xl overflow-hidden">
                        <div
                            class="w-28 h-28 rounded-full border-4 border-slate-50 z-10 group-hover:scale-150 group-hover:-translate-x-24 group-hover:-translate-y-20 transition-all duration-500 overflow-hidden">
                            <img src="img/cani.jpg" alt="Profile Image" class="w-full h-full object-cover">
                        </div>
                        <div class="z-10 group-hover:-translate-y-10 transition-all duration-500">
                            <span class="text-2xl font-semibold">Vichtor Vilson Klipp Cani</span>
                            <p>Desenvolvedor Back-end</p>
                        </div>
                    </div>
                    <div
                        class="group before:hover:scale-95 before:hover:h-72 before:hover:w-80 before:hover:h-44 before:hover:rounded-b-2xl before:transition-all before:duration-500 before:content-[''] before:w-80 before:h-24 before:rounded-t-2xl before:bg-gradient-to-bl from-sky-200 via-orange-200 to-orange-700 before:absolute before:top-0 w-80 h-72 relative bg-slate-50 flex flex-col items-center justify-center gap-2 text-center rounded-2xl overflow-hidden">
                        <div
                            class="w-28 h-28 rounded-full border-4 border-slate-50 z-10 group-hover:scale-150 group-hover:-translate-x-24 group-hover:-translate-y-20 transition-all duration-500 overflow-hidden">
                            <img src="img/winter.jpg" alt="Profile Image" class="w-full h-full object-cover">
                        </div>
                        <div class="z-10 group-hover:-translate-y-10 transition-all duration-500">
                            <span class="text-2xl font-semibold">Matheus Winter</span>
                            <p>Desenvolvedor Back-end</p>
                        </div>
                    </div>
                </div>
        </section>
    </main>
    <footer class="footer">
        <div class="contatos">
            <h3>Contatos</h3>
            <ul>
                <li><a href="mailto:infinity-tech@gmail.com"><i class='bx bx-envelope'></i>infinitytech@gmail.com</a>
                </li>
                <li><a href="https://www.instagram.com/sonnusterapia/"><i class='bx bxl-instagram'></i>
                        @infinity_tech</a></li>
                <li><a href="tel:+5547997802455"><i class='bx bx-phone'></i> (XX) XXXX-XXXX</a></li>
            </ul>
        </div>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menu = document.querySelector('.menu');
            const NavMenu = document.querySelector('.nav-menu');

            menu.addEventListener('click', () => {
                menu.classList.toggle('ativo');
                NavMenu.classList.toggle('ativo');
            });

            const priceRange = document.getElementById('price-range');
            const priceValue = document.getElementById('price-value');
            const servicoItems = document.querySelectorAll('.servico-item');
            const filterButtons = document.querySelectorAll('.filter-btn');

            const filterProducts = () => {
                const maxPrice = priceRange.value;
                const activeCategory = document.querySelector('.filter-btn.active')?.dataset.category || 'all';

                priceValue.textContent = maxPrice;

                servicoItems.forEach(item => {
                    const itemPrice = parseFloat(item.getAttribute('data-price'));
                    const itemCategory = item.getAttribute('data-category');
                    const priceMatch = itemPrice <= maxPrice;
                    const categoryMatch = activeCategory === 'all' || itemCategory === activeCategory;

                    if (priceMatch && categoryMatch) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            };

            priceRange.addEventListener('input', filterProducts);

            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    filterProducts();
                });
            });

            // Set initial filter state
            filterButtons[0].classList.add('active');
            priceRange.dispatchEvent(new Event('input'));
        });
        // JavaScript (script.js)
        function toggleCart() {
            var cartSidebar = document.getElementById("cartSidebar");
            if (cartSidebar.style.width === "250px") {
                cartSidebar.style.width = "0";
            } else {
                cartSidebar.style.width = "250px";
            }
        }
        document.querySelector('.search-bar').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                const query = document.querySelector('.search-bar').value;
                if (query) {
                    window.location.href = `/search?query=${query}`;
                }
            }
        });

        document.querySelector('.search-icon').addEventListener('click', function () {
            const query = document.querySelector('.search-bar').value;
            if (query) {
                window.location.href = `/search?query=${query}`;
            }
        });

    </script>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
</body>

</html>