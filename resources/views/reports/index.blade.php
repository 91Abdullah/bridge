@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-html5-1.5.4/b-print-1.5.4/r-2.2.2/datatables.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.css">
@endpush

@section('content')

    <div class="container-fluid">

        <div class="card">
            <div class="card-header">
                <h3>Call Detail Report</h3>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-lg-6 offset-lg-3">
                        <form action="" id="submitDate">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label" for="date">Select Date</label>
                                <div class="col-sm-10">
                                    <input name="date" id="datepicker" type="text" class="form-control" value="{{ \Carbon\Carbon::now()->format("Y-m-d") }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-8 offset-sm-2">
                                    <button class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <table class="table table-bordered" id="dataTable">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Source</th>
                        <th>Destination</th>
                        <th>Start</th>
                        <th>Answer</th>
                        <th>End</th>
                        <th>Duration</th>
                        <th>Billsec</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Recording</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="12" class="text-center">Select date to display data.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="playAudio" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Playing Audio</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <audio controls>
                        <source id="currentAudio">
                        Your browser does not support the audio element.
                    </audio>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/jszip-2.5.0/dt-1.10.18/b-1.5.4/b-colvis-1.5.4/b-html5-1.5.4/b-print-1.5.4/r-2.2.2/datatables.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script>

        const dataUrl = "{{ route('get.data') }}";
        const token = "{{ csrf_token() }}";
        const downloadUrl = "{{ route('download.file') }}";
        const appUrl = "{{ url("/") }}";
        const playFile = "{{ route('get.file') }}";
        let submitDate = document.getElementById('submitDate');
        let audioElem = document.getElementById("currentAudio");
        let dp = $('#datepicker');
        let modal = $('#playAudio');
        let table = undefined;

        $(document).ready(function () {

            dp.datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                orientation: "bottom"
            });

            dp.on("changeDate", function(e) {
                dp.val(e.target.value);
            });

            modal.on("show.bs.modal", loadModal);

            submitDate.onsubmit = submitForm;
        });

        function downloadFile(event) {
            console.log(event);
            const data = event.target.dataset.src;

            axios.post(downloadUrl, {
                file: data,
                _token: token
            })
                .then(function (response) {
                    console.log(response);
                })
                .catch(function (error) {
                    console.log(error);
                });
        }

        function registerButtons() {
            let files = document.getElementsByClassName('downloadFile');
            for (let i = 0; i < files.length; i++) {
                files[i].addEventListener("click", downloadFile);
            }
        }
        
        function loadModal(event) {
            //console.log(event);
            const data = event.relatedTarget.dataset.src;

            const url = axios.post(playFile, {
                file: data,
                _token: token
            })
                .then(function (response) {
                    console.log(response.data);
                    audioElem.src = appUrl + response.data;
                    audioElem.parentNode.load();
                })
                .catch(function (error) {
                    console.log(error);
                });

        }


        function submitForm(event) {
            event.preventDefault();

            if($.fn.dataTable.isDataTable('.table')) {
                table.destroy();
            }

            table = $("#dataTable").DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                paging: true,
                ajax: {
                    url: dataUrl,
                    type: "get",
                    data: {
                        _token: "{!! csrf_token() !!}",
                        date: dp.val()
                    }
                },
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'excel', 'pdf'
                ],
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'source', name: 'source'},
                    {data: 'destination', name: 'destination'},
                    {data: 'start', name: 'start'},
                    {data: 'answer', name: 'answer'},
                    {data: 'end', name: 'end'},
                    {data: 'duration', name: 'duration'},
                    {data: 'billsec', name: 'billsec'},
                    {data: 'dialstatus', name: 'dialstatus'},
                    {data: 'amount', name: 'amount'},
                    {data: 'bridged_call_id', name: 'bridged_call_id', fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                        $(nTd).html("<div class='btn-group'><button data-src='" + sData + "' data-toggle='modal' data-target='#playAudio' class='btn btn-primary'><i class='fas fa-play'></i> Play</button><button data-src='" + sData + "' class='btn btn-success downloadFile'><i class='fas fa-download'></i> Download</button></div>")
                    }}
                    // {data: 'bridged_call_id', name: 'bridged_call_id'}
                ],
                footerCallback: registerButtons
            });
        }

    </script>
@endpush
