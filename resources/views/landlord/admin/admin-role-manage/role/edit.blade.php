@extends(route_prefix().'admin.admin-master')
@section('title')
    {{__('Edit Role')}}
@endsection
@section('content')
    <div class="col-lg-12 col-ml-12">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="header-wrap d-flex justify-content-between">
                            <h4 class="header-title mb-4">{{__('Edit Role')}}</h4>
                            <div class="btn-wrapper">
                                <a href="{{route(route_prefix().'admin.all.admin.role')}}" class="btn btn-info">{{__('All Roles')}}</a>
                            </div>
                        </div>
                        <x-error-msg/>
                        <x-flash-msg/>
                        <form action="{{route(route_prefix().'admin.user.role.update')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{$role->id}}">
                            <div class="form-group">
                                <label for="name">{{__('Name')}}</label>
                                <input type="text" class="form-control"  value="{{$role->name}}" name="name" placeholder="{{__('Enter name')}}">
                            </div>
                            <button type="button" class="btn btn-xs mb-4 btn-outline-dark checked_all">{{__('Check All')}}</button>
                            <div class="row checkbox-wrapper">
                                @foreach($permissions ?? [] as $index => $permission)
                                    <div class="permission-prefix mt-4">
                                        <h4 class="text-capitalize">{{$index}}</h4>
                                        <hr>
                                    </div>

                                    @foreach($permission ?? [] as $perm)
                                        <div class="col-lg-2 col-md-2">
                                            <div class="form-group">
                                                <label ><strong>{{__(ucfirst(str_replace('-',' ',$perm->name)))}}</strong></label>
                                                <label class="switch role">
                                                    <input type="checkbox" name="permission[]" @if(in_array($perm->id, $rolePermissions)) checked @endif value="{{$perm->id}}" >
                                                    <span class="slider onff"></span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                            <button type="submit" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function (){
           "use strict";

           $(document).on('click','.checked_all',function (){
              var allCheckbox =  $('.checkbox-wrapper input[type="checkbox"]');
              $.each(allCheckbox,function (index,value){
                  if ($(this).is(':checked')){
                      $(this).prop('checked',false);
                  }else{
                      $(this).prop('checked',true);
                  }
              });
           });

        });
    </script>
@endsection
