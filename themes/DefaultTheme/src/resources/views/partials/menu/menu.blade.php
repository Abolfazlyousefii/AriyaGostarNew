<nav class="main-menu dt-sl ariya-main-menu">
    <ul class="list hidden-md ariya-desktop-menu-list">
        @foreach($menus as $menu)
            @include('front::partials.menu.child-menu')
        @endforeach
    </ul>

    @include('front::partials.mobile-menu.menu')
</nav>
