@php
    $variables      = get_widget($widget);
    $main_sliders   = $variables['fullscreen_slider'];
    $mobile_sliders = $variables['mobile_sliders'];
    $responsive_sliders = $mobile_sliders->count() ? $mobile_sliders : $main_sliders;
@endphp

<div class="ariya-full-slider-wrap">
    @if ($main_sliders->count())
        <section id="main-slider" class="main-slider carousel slide carousel-fade hidden-sm ariya-draggable-carousel" data-ride="carousel">
            <ol class="carousel-indicators">
                @foreach ($main_sliders as $slider)
                    <li data-target="#main-slider" data-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}"></li>
                @endforeach
            </ol>
            <div class="carousel-inner">
                @foreach ($main_sliders as $slider)
                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                        <a class="main-slider-slide" href="{{ $slider->link }}" draggable="false">
                            <img src="{{ asset($slider->image) }}" alt="{{ $slider->title }}" draggable="false">
                        </a>
                    </div>
                @endforeach
            </div>
            <a class="carousel-control-prev" href="#main-slider" role="button" data-slide="prev"><i class="mdi mdi-chevron-right"></i></a>
            <a class="carousel-control-next" href="#main-slider" role="button" data-slide="next"><i class="mdi mdi-chevron-left"></i></a>
        </section>
    @endif

    @if ($responsive_sliders->count())
        <section id="main-slider-res" class="main-slider carousel slide carousel-fade d-none show-sm ariya-draggable-carousel" data-ride="carousel">
            <ol class="carousel-indicators">
                @foreach ($responsive_sliders as $slider)
                    <li data-target="#main-slider-res" data-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}"></li>
                @endforeach
            </ol>
            <div class="carousel-inner">
                @foreach ($responsive_sliders as $slider)
                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                        <a class="main-slider-slide" href="{{ $slider->link }}" draggable="false">
                            <img src="{{ asset($slider->image) }}" alt="{{ $slider->title }}" draggable="false">
                        </a>
                    </div>
                @endforeach
            </div>
            <a class="carousel-control-prev" href="#main-slider-res" role="button" data-slide="prev"><i class="mdi mdi-chevron-right"></i></a>
            <a class="carousel-control-next" href="#main-slider-res" data-slide="next"><i class="mdi mdi-chevron-left"></i></a>
        </section>
    @endif
</div>
