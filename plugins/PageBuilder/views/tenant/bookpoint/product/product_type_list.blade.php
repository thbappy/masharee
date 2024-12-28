<!-- Explore Category area starts -->
<section class="explore-category-area digital-explore-category-area padding-top-100 padding-bottom-50"
         data-padding-top="{{$data['padding_top']}}"
         data-padding-bottom="{{$data['padding_bottom']}}">
    <div class="container custom-container-one">
        <div class="section-title text-left">
            <h2 class="title"> {{$data['title']}} </h2>
            <div class="btn-wrapper">
                <a href="{{$data['view_all_url']}}"
                   class="view-all color-heading fw-500"> {{$data['view_all_text']}} </a>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-lg-12 mt-4">
                <div class="category-list-wrapper">
                    @php
                        $all = !empty($data['categories']) ? $data['categories']->pluck('id')->toArray() : '';
                        $allIds = implode(',', $all);
                    @endphp
                    <ul class="category-grid-list filter-list store-tabs">
                        <li class="list active" data-limit="{{$data['product_limit']}}"
                            data-tab="all" data-all-id="{{$allIds}}" data-sort_by="{{$data['sort_by']}}"
                            data-sort_to="{{$data['sort_to']}}"> {{__('All')}} </li>
                        @foreach($data['categories'] as $category)
                            <li class="list" data-tab="{{$category->slug}}"
                                data-limit="{{$data['product_limit']}}" data-sort_by="{{$data['sort_by']}}"
                                data-sort_to="{{$data['sort_to']}}"> {{$category->name}} </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="imageloaded">
            <div class="row grid-category mt-4 markup_wrapper">
                @foreach($data['products'] as $product)
                    @php
                        $data_info = get_digital_product_dynamic_price($product);
                        $regular_price = $data_info['regular_price'];
                        $sale_price = $data_info['sale_price'];
                        $discount = $data_info['discount'];

                        $price = $sale_price > 0 ? $sale_price : $regular_price;
                    @endphp
                    <div class="grid-item col-xxl-4 col-lg-6 mt-4">
                        <div class="global-flex-card hover-overlay featured-card-padding radius-10">
                            <div class="global-flex-card-thumb radius-5">
                                <a href="{{route('tenant.shop.product.details', $product->slug)}}">
                                    {!! render_image_markup_by_attachment_id($product->image_id, 'product-image') !!}
                                </a>
                            </div>
                            <a href="javascript:void(0)"
                               class="product-cart-btn cart-btn-absolute radius-5 digital-add-to-cart-btn"
                               data-product_id="{{ $product->id }}"> {{__('Add to Cart')}} </a>
                            <a href="{{route('tenant.digital.shop.product.details', $product->slug)}}"
                               class="cart-details-btn cart-details-absolute radius-5">{{__('View Details')}}</a>
                            <div class="global-flex-card-contents">
                                @if($discount > 0)
                                    <div class="global-badge">
                                        <span class="global-badge-box bg-one"> {{$discount}}% {{__('off')}} </span>
                                    </div>
                                @endif

                                @if(!empty($product->additionalFields?->badge_id))
                                    <div class="global-badge">
                                        <span
                                            class="global-badge-box bg-new"> {{$product->additionalFields?->badge?->name}} </span>
                                    </div>
                                @endif

                                <h6 class="global-flex-card-contents-title">
                                    <a href="{{route('tenant.digital.shop.product.details', $product->slug)}}"> {{Str::words($product->name, 4)}} </a>
                                </h6>

                                <span
                                    class="global-flex-card-contents-subtitle mt-2"> {{$product->additionalFields?->author?->name}} </span>

                                {!! render_product_star_rating_markup_with_count($product) !!}

                                <div class="price-update-through mt-3">
                                    <span
                                        class="fs-24 fw-500 flash-prices color-one"> {{float_amount_with_currency_symbol($price)}} </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
<!-- Explore Category area end -->
