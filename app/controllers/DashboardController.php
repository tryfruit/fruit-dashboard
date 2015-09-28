<?php


/**
 * --------------------------------------------------------------------------
 * DashboardController: Handles the authentication related sites
 * --------------------------------------------------------------------------
 */
class DashboardController extends BaseController
{
    /**
     * ================================================== *
     *                   PUBLIC SECTION                   *
     * ================================================== *
     */

    /**
     * anyDashboard
     * --------------------------------------------------
     * returns the user dashboard, or redirects to signup wizard
     * @return Renders the dashboard page
     * --------------------------------------------------
     */
    public function anyDashboard() {
        $time = microtime(true);
        /* Check the default dashboard and create if not exists */
        Auth::user()->checkOrCreateDefaultDashboard();

        /* Checking the user's data managers integrity */
        Auth::user()->checkDataManagersIntegrity();

        /* Checking the user's widgets integrity */
        Auth::user()->checkWidgetsIntegrity();

        /* Get active dashboard, if the url contains it */
        $parameters = array();
        $activeDashboard = Request::query('active');
        if ($activeDashboard) {
            $parameters['activeDashboard'] = $activeDashboard;
        }

        /* Creating view */
        $view = View::make('dashboard.dashboard', $parameters);

        try {
            /* Trying to render the view. */
            return $view->render();
        } catch (Exception $e) {
            /* Error occured trying to find the widget. */
            Auth::user()->turnOffBrokenWidgets();
            /* Recreating view. */
            $view = View::make('dashboard.dashboard', $parameters);
        }
        return $view;

    }

    /**
     * getManageDashboards
     * --------------------------------------------------
     * @return Renders the mange dashboards page
     * --------------------------------------------------
     */
    public function getManageDashboards() {
        /* Check the default dashboard and create if not exists */
        Auth::user()->checkOrCreateDefaultDashboard();

        /* Render the page */
        return View::make('dashboard.manage-dashboards');
    }

    /**
     * anyDeleteDashboard
     * --------------------------------------------------
     * @return Deletes a dashboard.
     * --------------------------------------------------
     */
    public function anyDeleteDashboard($dashboardId) {
        /* Get the dashboard */
        $dashboard = $this->getDashboard($dashboardId);
        if (is_null($dashboard)) {
            return Response::json(FALSE);
        }

        /* Track event | DELETE DASHBOARD */
        $tracker = new GlobalTracker();
        $tracker->trackAll('lazy', array(
            'en' => 'Dashboard deleted',
            'el' => $dashboard->name)
        );

        /* Delete the dashboard*/
        $dashboard->delete();

        /* Return. */
        return Response::json(TRUE);
    }

    /**
     * anyLockDashboard
     * --------------------------------------------------
     * @return Locks a dashboard.
     * --------------------------------------------------
     */
    public function anyLockDashboard($dashboardId) {
        $dashboard = $this->getDashboard($dashboardId);
        if (is_null($dashboard)) {
            return Response::json(FALSE);
        }

        $dashboard->is_locked = TRUE;
        $dashboard->save();

        /* Return. */
        return Response::json(TRUE);
    }

    /**
     * anyUnlockDashboard
     * --------------------------------------------------
     * @return Unlocks a dashboard.
     * --------------------------------------------------
     */
    public function anyUnlockDashboard($dashboardId) {
        $dashboard = $this->getDashboard($dashboardId);
        if (is_null($dashboard)) {
            return Response::json(FALSE);
        }

        $dashboard->is_locked = FALSE;
        $dashboard->save();

        /* Return. */
        return Response::json(TRUE);
    }

    /**
     * anyMakeDefault
     * --------------------------------------------------
     * @return Makes a dashboard the default one.
     * --------------------------------------------------
     */
    public function anyMakeDefault($dashboardId) {
        // Make is_default false for all dashboards
        foreach (Auth::user()->dashboards()->where('is_default', TRUE)->get() as $oldDashboard) {
            Log::info($oldDashboard->id);
            $oldDashboard->is_default = FALSE;
            $oldDashboard->save();
        }

        $dashboard = $this->getDashboard($dashboardId);
        if (is_null($dashboard)) {
            return Response::json(FALSE);
        }

        $dashboard->is_default = TRUE;
        $dashboard->save();

        /* Return. */
        return Response::json(TRUE);
    }

    /**
     * postRenameDashboard
     *
     */
    public function postRenameDashboard($dashboardId) {
        $dashboard = $this->getDashboard($dashboardId);
        if (is_null($dashboard)) {
            return Response::json(FALSE);
        }

        $newName = Input::get('dashboard_name');
        if (is_null($newName)) {
            return Response::json(FALSE);
        }

        $dashboard->name = $newName;
        $dashboard->save();

        /* Return. */
        return Response::json(TRUE);
    }

    /**
     * postCreateDashboard
     *
     */
    public function postCreateDashboard() {
        $name = Input::get('dashboard_name');
        if (empty($name)) {
            return Response::json(FALSE);
        }

        /* Creating dashboard. */
        $dashboard = new Dashboard(array(
            'name'       => $name,
            'background' => TRUE,
            'number'     => Auth::user()->dashboards->max('number') + 1
        ));
        $dashboard->user()->associate(Auth::user());
        $dashboard->save();

        /* Track event | ADD DASHBOARD */
        $tracker = new GlobalTracker();
        $tracker->trackAll('lazy', array(
            'en' => 'Dashboard added',
            'el' => $dashboard->name)
        );

        /* Return. */
        return Response::json(TRUE);
    }

    /**
     * anyGetDashboards
     * --------------------------------------------------
     * @return array
     * --------------------------------------------------
     */
    public function anyGetDashboards() {
        $dashboards = array();
        foreach (Auth::user()->dashboards as $dashboard) {
            array_push($dashboards, array(
                'id'        => $dashboard->id,
                'name'      => $dashboard->name,
                'is_locked' => $dashboard->is_locked,
            ));
        }

        /* Return. */
        return Response::json($dashboards);
    }

    /**
     * ================================================== *
     *                   PRIVATE SECTION                  *
     * ================================================== *
     */

    /**
     * getDashboard
     * --------------------------------------------------
     * @return Dashboard
     * --------------------------------------------------
     */
    private function getDashboard($dashboardId) {
        $dashboard = Dashboard::find($dashboardId);
        if (is_null($dashboard)) {
            return NULL;
        }
        if ($dashboard->user != Auth::user()) {
            return NULL;
        }
        return $dashboard;
    }

} /* DashboardController */
