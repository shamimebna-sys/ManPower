<div>

    @if (session()->has('message'))
        <div class="alert alert-success" role="alert">{{ session('message') }}</div>
    @endif

    @error('name') <div class="alert alert-danger" role="alert"> {{ $message }} </div> @enderror

    <form action="#" wire:submit.prevent="register">


        <div class="row mb-30">
            <div class="col-lg-12">
                <div class="row">

                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                        <!-- Single Input Start -->
                        <div class="single-input mb-25">
                            <label for="first-name">Full Name <span>*</span></label>
                            <input type="text" id="first-name" name="first-name" placeholder="First Name" value="Jhon"  wire:model="name" >
                        </div>
                        <!-- Single Input End -->
                    </div>

                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                        <!-- Single Input Start -->
                        <div class="single-input mb-25">
                            <label for="last-name">Father Name <span>*</span></label>
                            <input type="text" id="last-name" name="last-name" placeholder="Father Name" value=""  wire:model="father_name">
                        </div>
                        <!-- Single Input End -->
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                        <!-- Single Input Start -->
                        <div class="single-input mb-25">
                            <label for="last-name">Mother Name <span>*</span></label>
                            <input type="text" id="last-name" name="last-name" placeholder="Mother Name" value=""  wire:model="mother_name">
                        </div>
                        <!-- Single Input End -->
                    </div>

                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                        <!-- Single Input Start -->
                        <div class="single-input mb-25">
                            <label for="email">Email <span>*</span></label>
                            <input type="email"  placeholder="Enter your Email" value=""  wire:model="email">
                        </div>
                        <!-- Single Input End -->
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                        <!-- Single Input Start -->
                        <div class="single-input mb-25">
                            <label for="email">Mobile <span>*</span></label>
                            <input type="text"   placeholder="Enter your mobile"  value="" wire:model="mobile">
                        </div>
                        <!-- Single Input End -->
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                        <!-- Single Input Start -->
                        <div class="single-input mb-25">
                            <label for="email">Alternate Mobile Number <span>*</span></label>
                            <input type="text"  placeholder="Enter your mobile"  value="" wire:model="secondary_mobile">
                        </div>
                        <!-- Single Input End -->
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                        <!-- Single Input Start -->
                        <div class="single-input mb-25">
                            <label for="email">Date Of Birth <span>*</span></label>
                            <input type="date"  placeholder="Enter your Date Of Birth"  value="" wire:model="bid">
                        </div>
                        <!-- Single Input End -->
                    </div>

                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                        <!-- Single Input Start -->
                        <div class="single-input mb-25">
                            <label for="email">Upload Full Photo <span>*</span></label>
                            <input type="file"  placeholder="Enter your mobile"  value="" wire:model="full_photo_file_path">
                        </div>
                        <!-- Single Input End -->
                    </div>

                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                        <!-- Single Input Start -->
                        <div class="single-input mb-25">
                            <label for="email">Upload NID Card Copy <span>*</span></label>
                            <input type="file"  placeholder="Enter your mobile"  value="" wire:model="nid_file_path">
                        </div>
                        <!-- Single Input End -->
                    </div>

                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                        <!-- Single Input Start -->
                        <div class="single-input mb-25">
                            <label for="email">Upload Passport Copy <span>*</span></label>
                            <input type="file"  placeholder="Enter your mobile"  value="" wire:model="passport_file_path">
                        </div>
                        <!-- Single Input End -->
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                        <!-- Single Input Start -->
                        <div class="single-input mb-25">
                            <label for="email">Upload BID Certificate <span>*</span></label>
                            <input type="file"  placeholder="Enter your mobile"  value="" wire:model="bid_file_path">
                        </div>
                        <!-- Single Input End -->
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                        <!-- Single Input Start -->
                        <div class="single-input mb-25">
                            <label for="email">Upload CV <span>*</span></label>
                            <input type="file"  placeholder="Enter your mobile"  value="" wire:model="cv_file_path">
                        </div>
                        <!-- Single Input End -->
                    </div>


                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                        <!-- Single Input Start -->
                        <div class="single-input mb-25">
                            <label for="address-one">Present Address</label>
                            <textarea type="text" id="address-two" name="address-two" placeholder="Enter your Present Address"  value="" wire:model="present_address_house"> </textarea>
                        </div>
                        <!-- Single Input End -->
                    </div>

                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                        <!-- Single Input Start -->
                        <div class="single-input mb-25">
                            <label for="address-two">Permanent Address</label>
                            <textarea type="text" id="address-two" name="address-two" placeholder="Enter your Permanent Address"  value="" wire:model="permanent_address_house"> </textarea>
                        </div>
                        <!-- Single Input End -->
                    </div>

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="profile-action-btn d-flex flex-wrap align-content-center justify-content-between">
                    <button class="ht-btn theme-btn theme-btn-two mb-xs-20">Submit</button>
                </div>
            </div>
        </div>
    </form>
</div>
