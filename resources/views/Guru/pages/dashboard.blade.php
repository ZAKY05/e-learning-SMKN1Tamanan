@extends('Guru.layout.master')

@section('content')
    <div class="main-content">
        <div class="row">
            <div class="card-footer">
                <div class="row g-4">
                    <div class="col-lg-3">
                        <div class="p-3 border border-dashed rounded">
                            <div class="fs-12 text-muted mb-1">Guru</div>
                            <h6 class="fw-bold text-dark">$5,486</h6>
                            <div class="progress mt-2 ht-3">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 81%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="p-3 border border-dashed rounded">
                            <div class="fs-12 text-muted mb-1">Completed</div>
                            <h6 class="fw-bold text-dark">$9,275</h6>
                            <div class="progress mt-2 ht-3">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 82%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="p-3 border border-dashed rounded">
                            <div class="fs-12 text-muted mb-1">Rejected</div>
                            <h6 class="fw-bold text-dark">$3,868</h6>
                            <div class="progress mt-2 ht-3">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 68%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="p-3 border border-dashed rounded">
                            <div class="fs-12 text-muted mb-1">Revenue</div>
                            <h6 class="fw-bold text-dark">$50,668</h6>
                            <div class="progress mt-2 ht-3">
                                <div class="progress-bar bg-dark" role="progressbar" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Payment Records] start -->
            <div class="col-xxl-8">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Payment Record</h5>
                        <div class="card-header-action">
                            <div class="card-header-btn">
                                <div data-bs-toggle="tooltip" title="Refresh">
                                    <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning"
                                        data-bs-toggle="refresh"> </a>
                                </div>
                                <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                    <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                        data-bs-toggle="expand"> </a>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card-body custom-card-action p-0">
                        <div id="payment-records-chart"></div>
                    </div>

                </div>
            </div>

            <!--! END: [Team Progress] !-->
        </div>
    </div>
@endsection
