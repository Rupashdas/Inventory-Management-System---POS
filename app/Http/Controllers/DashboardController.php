<?php

namespace App\Http\Controllers;

class DashboardController extends Controller {

    public function dashboardPage() {
        return view('pages.dashboard.dashboard-page');
    }

}
