<nav class="space-y-6">
    @php $user = auth()->user(); @endphp

    <!-- 1. Cuisine & Bar -->
    @if(!$user || ($user && ($user->hasRoleKey('owner') || $user->hasRoleKey('manager') || $user->hasRoleKey('chef'))))
    <div>
        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
            👨‍🍳 Cuisine & Bar
        </h3>
        <div class="space-y-1">
            <a class="block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" href="{{ route('kds.kitchen') }}" target="_blank" data-turbolinks="false">
                Ecran Cuisine
            </a>
            <a class="block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800" href="{{ route('kds.bar') }}" target="_blank" data-turbolinks="false">
                Ecran Bar
            </a>
        </div>
    </div>
    @endif

    <!-- 2. Restaurant -->
    @if(!$user || ($user && ($user->hasRoleKey('owner') || $user->hasRoleKey('manager') || $user->hasRoleKey('waiter'))))
    <div>
        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
            🍽️ Restaurant
        </h3>
        <div class="space-y-1">
            <a class="block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('restaurant.index') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}" href="{{ route('restaurant.index') }}">
                Tableau de bord
            </a>
            <a class="block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('restaurant.pos.index') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}" href="{{ route('restaurant.pos.index') }}">
                Terminal Point de Vente
            </a>
            <a class="block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.reservations*') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}" href="{{ route('admin.reservations.index') }}">
                Réservations
            </a>
            <a class="block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.floors*') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}" href="{{ route('admin.floors.index') }}">
                Plans de Salle
            </a>
        </div>
    </div>
    @endif

    <!-- 2. Katalog ve Üretim -->
    @if(!$user || ($user && ($user->hasRoleKey('owner') || $user->hasRoleKey('manager') || $user->hasRoleKey('chef'))))
    <div>
        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
            🍴 Menü Yönetimi
        </h3>
        <div class="space-y-1">
            <a class="block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.categories*') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}" href="{{ route('admin.categories.index') }}">
                Catégories
            </a>
            <a class="block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.products*') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}" href="{{ route('admin.products.index') }}">
                Menu & Recettes
            </a>
            <a href="{{ route('admin.qr-codes.index') }}" class="block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.qr-codes.*') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}">
                Gestion QR Codes
            </a>
        </div>
    </div>
    @endif

    <!-- 3. Lojistik ve Tedarik -->
    @if(!$user || ($user && ($user->hasRoleKey('owner') || $user->hasRoleKey('manager') || $user->hasRoleKey('chef') || $user->hasRoleKey('bartender'))))
    <div>
        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
            📦 Stock & Approvisionnement
        </h3>
        <div class="space-y-1">
            <a class="block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.inventory*') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}" href="{{ route('admin.inventory.index') }}">
                Stock / Inventaire
            </a>
            <a class="block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.shopping_list*') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}" href="{{ route('admin.shopping_list') }}">
                Liste de Courses 🛒
            </a>
            <a class="block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.suppliers*') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}" href="{{ route('admin.suppliers.index') }}">
                Fournisseurs
            </a>
        </div>
    </div>
    @endif

    <!-- 4. Yönetim ve Sistem -->
    @if(!$user || ($user && ($user->hasRoleKey('owner') || $user->hasRoleKey('manager'))))
    <div>
        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
            ⚙️ Administration
        </h3>
        <div class="space-y-1">
            <a class="block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}" href="{{ route('admin.dashboard') }}">
                Tableau de bord
            </a>
            <a class="block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.users') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}" href="{{ route('admin.users') }}">
                Utilisateurs
            </a>
            <a class="block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.roles_permissions') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}" href="{{ route('admin.roles_permissions') }}">
                Rôles & Permissions
            </a>
            <a class="block px-3 py-2 rounded text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.settings*') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}" href="{{ route('admin.settings') }}">
                Paramètres
            </a>
        </div>
    </div>
    @endif
</nav>
