@extends(route_prefix().'admin.admin-master')
@section('title')
    {{__('All Pages')}}
@endsection
@section('style')
    <x-datatable.css/>
    <style>
        .upload-layout {
            background-color: #18dcff;
            border-color: #18dcff;
        }
    </style>
@endsection
@section('content')
    <div class="col-12 stretch-card">
        <div class="card">
            <div class="card-body">
                <x-error-msg/>
                <x-flash-msg/>

                <x-admin.header-wrapper>
                    <x-slot name="left">
                        <h4 class="card-title mb-4">{{__('All Pages')}}</h4>
                    </x-slot>
                    <x-slot name="right" class="d-flex">
                        <x-link-with-popover permissions="page-create"
                                             url="{{route(route_prefix().'admin.pages.create')}}" extraclass="ml-3">
                            {{__('Create New Page')}}
                        </x-link-with-popover>
                    </x-slot>
                </x-admin.header-wrapper>

                <x-datatable.table>
                    <x-slot name="th">
                        <th class="no-sort">{{__('ID')}}</th>
                        <th>{{__('Title')}}</th>
                        <th>{{__('Status')}}</th>
                        <th>{{__('Created')}}</th>
                        <th>{{__('Action')}}</th>
                    </x-slot>
                    <x-slot name="tr">
                        @foreach($all_pages as $page)
                            @php
                                $selected_page = get_static_option('home_page');
                                $selected_text = $page->id == $selected_page ? __('Current Home') : '';
                            @endphp
                            <tr>
                                <td>{{ $page->id }}</td>
                                <td>
                                    <span>
                                      {{__($page->title)}}
                                    </span>

                                    @if($selected_text)
                                        <small class="mx-2 badge badge-info">{{$selected_text}}</small>
                                    @endif
                                </td>
                                <td>{{ \App\Enums\StatusEnums::getText($page->status)  }}</td>
                                <td>{{$page->created_at->format('D, d-m-y')}}</td>
                                <td>
                                    @if(!$selected_text)
                                        <x-delete-popover permissions="page-delete" url="{{route(route_prefix().'admin.pages.delete', $page->id)}}"/>
                                    @endif

                                    <x-link-with-popover permissions="page-edit"
                                                         url="{{route(route_prefix().'admin.pages.edit', $page->id)}}">
                                        <i class="mdi mdi-pencil"></i>
                                    </x-link-with-popover>
                                    <x-link-with-popover target="_blank" class="info"
                                                         url="{{route(route_prefix().'dynamic.page', $page->slug)}}"
                                                         popover="{{__('view item in frontend')}}">
                                        <i class="mdi mdi-eye"></i>
                                    </x-link-with-popover>
                                    @if($page->page_builder === 1)
                                        <x-link-with-popover class="dark"
                                                             url="{{route(route_prefix().'admin.pages.builder', $page->id)}}"
                                                             popover="{{__('edit with page builder')}}">
                                            <i class="mdi mdi-settings"
                                               style="vertical-align: text-bottom"></i> {{__('Edit with Page-Builder')}}
                                        </x-link-with-popover>
                                    @endif

                                    <a tabindex="0" class="btn btn-primary btn-xs mb-3 mr-1 swal_change_button"
                                       data-bs-toggle="tooltip" data-bs-placement="top" title="{{__('Set as Home')}}"
                                    >
                                        <i>H</i>
                                    </a>

                                    <form method='post' action='{{route(route_prefix().'admin.general.page.settings.home')}}' class="swal_change_form d-none">
                                        <input type='hidden' name='_token' value='{{csrf_token()}}'>
                                        <input type='hidden' name='home_page' value="{{$page->id}}">
                                        <button type="submit" class="swal_form_submit_btn d-none"></button>
                                    </form>

                                    @if(tenant())
                                        <x-link-with-popover class="success"
                                                             url="{{route(route_prefix().'admin.pages.download', $page->id)}}"
                                                             popover="{{__('Download Page Layout')}}">
                                            <i class="mdi mdi-download"></i>
                                        </x-link-with-popover>

                                        <x-modal.button :target="'upload-modal'" dataid="{{$page->id}}"
                                                        :type="'secondary upload-layout upload-layout-btn'">
                                            <i class="mdi mdi-upload"></i>
                                        </x-modal.button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </x-slot>
                </x-datatable.table>

            </div>
        </div>
    </div>

    <x-modal.markup :target="'upload-modal'" :title="'Upload Page Layout'">
        <form action="{{route('tenant.admin.pages.upload')}}" method="POST" enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="page_id" value="">
            <div class="form-group">
                <label for="json_file">{{__('Upload JSON File')}}</label>
                <input type="file" class="form-control" id="json_file" name="page_layout">
            </div>

            <div class="form-group text-end">
                <button type="submit" class="btn btn-success btn-sm">{{__('Upload')}}</button>
            </div>
        </form>
    </x-modal.markup>
@endsection
@section('scripts')
    <x-datatable.js/>
    <script>
        $(document).ready(function ($) {
            "use strict";

            $(document).on('click', '.upload-layout-btn', function (){
                let el = $(this);
                let page_id = el.data('id');

                let modal = $('#upload-modal');
                modal.find('input[name=page_id]').val(page_id);
            })


            $(document).on('click', '#upload-modal button[type=submit]', function (e){
                e.preventDefault();

                Swal.fire({
                    title: '<strong style="color:red">{{ __('Are you sure?') }}</strong>',
                    text: '{{ __('Previous layout along with its data will be removed permanently if you upload this new layout!') }}',
                    icon: 'warning',
                    iconColor: 'red',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '{{__('Yes, upload it!')}}',
                    cancelButtonText: "{{__('Cancel')}}",
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).closest('form').trigger('submit');
                    }
                });
            })

            $(document).on('click', '.swal_change_button', function (e){
                e.preventDefault();
                Swal.fire({
                    title: '{{ __('Are you sure?') }}',
                    text: '{{ __('You can revert this item anytime!') }}',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#989898',
                    confirmButtonText: '{{__('Yes, Change it!')}}',
                    cancelButtonText: "{{__('Cancel')}}",
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).siblings('form.swal_change_form').trigger('submit');
                    }
                });
            })
        });
    </script>
@endsection
