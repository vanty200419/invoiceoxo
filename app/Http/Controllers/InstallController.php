<?php
namespace App\Http\Controllers;
use App\Http\Middleware\RedirectIfInstalled;
use App\Http\Requests\InstallRequest;
use App\Install\App;
use App\Install\Database;
use App\Install\Requirement;
use App\Classes\lb_helper;
use DotenvEditor;
use Illuminate\Contracts\Cache\Factory;
use Illuminate\Http\Request;
use Exception;
use PDOException;

class InstallController extends Controller
{
    public function __construct()
    {
        $this->middleware(RedirectIfInstalled::class);
    }
    public function install(Requirement $requirement)
    {
        return view('install.installation', compact('requirement'));
    }

    public function dbsettings(Request $request)
    {
        
        $status =  true;
        $message =  "";
        return view('install.dbsettings',compact('status','message'));
    }
    
    public function postDatabase(
        InstallRequest $request,
        Database $database,
        App $app,
        Factory $cache
    )
    {
        set_time_limit(3000);
        try {
            try {
                $database->setup($request->db);
            } catch (PDOException $pe) {
                return back()->withInput()
                    ->with('error', $pe->getMessage());
            }

            $app->setup();
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with(['message' => $e->getMessage()]);
        }
        return redirect('install/completed');
    }


    public function install_completed()
    {
        if (config('app.installed')) {
            return redirect()->route('admin-login');
        }

        DotenvEditor::setKey('APP_INSTALLED', 'true')->save();
        unlink(base_path('install'));
        return view('install.completed');
    }
}