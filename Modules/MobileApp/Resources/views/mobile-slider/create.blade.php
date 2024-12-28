@extends('tenant.admin.admin-master')
@section('title')
    {{__('Mobile Slider')}}
@endsection
@section('style')
    <x-media-upload.css/>
    <x-datatable.css />
    <x-bulk-action.css />
@endsection
@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-lg-12">
                <div class="margin-top-40">
                    <x-flash-msg/>
                    <x-error-msg/>
                </div>
                <div class="text-end">
                    <a class="btn btn-info" href="{{ route("tenant.admin.mobile.slider.all") }}">{{__('List')}}</a>
                </div>
            </div>
            <div class="col-lg-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">{{__('Add new mobile slider')}}</h4>
                        <form action="{{ route("tenant.admin.mobile.slider.create") }}" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="title">{{__('Title')}}</label>
                                <input class="form-control" id="title" name="title"
                                       placeholder="{{__('Mobile Slider Title...')}}" value="{{old('title')}}"/>
                            </div>
                            <div class="form-group">
                                <label for="description">{{__('Description')}}</label>
                                <textarea class="form-control" id="description" name="description"
                                          placeholder="{{__('Mobile Slider Description...')}}" rows="10">{{old('description')}}</textarea>
                            </div>

                            <x-fields.media-upload :title="__('Image')" :name="'image'" :dimentions="'1280x1280'"/>

                            <div class="form-group">
                                <label for="button_text">{{__('Button Text')}}</label>
                                <input class="form-control" id="button_text" name="button_text"
                                       placeholder="{{__('Mobile Slider Button Text...')}}" value="{{old('button_text')}}"/>
                            </div>

                            <div class="form-group">
                                <label for="button_url">{{__('Button URL')}}</label>
                                <input class="form-control" id="button_url" name="button_url"
                                       placeholder="{{__('Mobile Slider Button URL...')}}" value="{{old('button_url')}}"/>
                            </div>

                            <div class="form-group">
                                <label for="category">{{__('Enable Category')}}</label>
                                <input type="checkbox" id="category" name="category_type"/>
                            </div>

                            <div class="form-group" id="campaign-list">
                                <label for="campaigns">{{__('Select Campaign')}}</label>
                                <select id="campaigns" name="campaign" class="form-control nice-select wide">
                                    <option value="">{{__('Select Campaign')}}</option>
                                    @foreach($campaigns as $campaign)
                                        <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group" id="category-list" style="display: none">
                                <label for="products">{{__('Select Category')}}</label>
                                <select id="products" name="category" class="form-control">
                                    <option value="">{{__('Select Category')}}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <button class="btn btn-info">{{__('Create')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-media-upload.markup/>
@endsection
@section('scripts')
    <x-media-upload.js/>
    <script>
        $("#category").on("change",function (){
            if($(this).is(":checked")){
                $("#campaign-list").fadeOut();
                setTimeout(function (){
                    $("#category-list").fadeIn();
                },400);
            }else{
                $("#category-list").fadeOut();
                setTimeout(function (){
                    $("#campaign-list").fadeIn();
                }, 400);
            }
        });
    </script>
@endsection
