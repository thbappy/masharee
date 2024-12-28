@extends(route_prefix().'admin.admin-master')
@section('title')
    {{__('Add New Role')}}
@endsection
@section('content')
    <style>
        .checked_all:hover, .checked_all:active {
            color: #ffffff;
        }
    </style>
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="header-wrap d-flex justify-content-between">
                            <h4 class="header-title mb-4">{{__('New Role')}}</h4>
                            <div class="btn-wrapper">
                                <a href="{{route(route_prefix().'admin.all.admin.role')}}"
                                   class="btn btn-info mb-2">{{__('All Roles')}}</a>
                            </div>
                        </div>
                        <x-error-msg/>
                        <x-flash-msg/>
                        <form action="{{route(route_prefix().'admin.role.new')}}" method="post"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="name">{{__('Name')}}</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       placeholder="{{__('Enter name')}}">
                            </div>
                            <button type="button"
                                    class="btn btn-xs mb-4 btn-outline-dark checked_all">{{__('Check All')}}</button>
                            <div class="row checkbox-wrapper">
                                @foreach($permissions ?? [] as $index => $permission)
                                    <div class="permission-prefix mt-4">
                                        <h4 class="text-capitalize">{{$index}}</h4>
                                        <hr>
                                    </div>

                                    @foreach($permission ?? [] as $perm)
                                        <div class="col-lg-2 col-md-2">
                                            <div class="form-group">
                                                <label><strong>{{__(ucfirst(str_replace('-',' ',$perm->name)))}}</strong></label>
                                                <label class="switch role">
                                                    <input type="checkbox" name="permission[]" value="{{$perm->id}}">
                                                    <span class="slider onff"></span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                            <button type="submit" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Submit')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function () {
            "use strict";

            var currrentState = false;
            $(document).on('click', '.checked_all', function () {
                var allCheckbox = $('.checkbox-wrapper input[type="checkbox"]');
                $.each(allCheckbox, function (index, value) {
                    $(this).prop('checked', !currrentState);
                });
                currrentState = !currrentState;
                currrentState ? $(this).text('{{__('Uncheck All')}}') : $(this).text('{{__('Check All')}}');
            });
        });
    </script>
@endsection
