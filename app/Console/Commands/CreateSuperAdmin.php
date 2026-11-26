<?php
namespace App\Console\Commands;

use App\Models\User;
use App\Services\RoleAssignmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    protected $signature = 'shypatap:create-superadmin {email} {--name=SuperAdmin}';
    protected $description = 'ŞypaTap üçin täze SuperAdmin hasabyny döredýär.';

    public function handle(RoleAssignmentService $roles): int
    {
        $email=strtolower(trim((string)$this->argument('email')));
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)){ $this->error('Dogry email giriziň.'); return self::FAILURE; }
        if(User::withTrashed()->where('email',$email)->exists()){ $this->error('Bu email eýýäm ulanylýar.'); return self::FAILURE; }
        $password=$this->secret('SuperAdmin üçin täze parol giriziň (iň azyndan 12 simwol)');
        if(strlen((string)$password)<12){$this->error('Parol azyndan 12 simwoldan ybarat bolmaly.');return self::FAILURE;}
        $user=User::create(['name'=>(string)$this->option('name'),'email'=>$email,'password'=>Hash::make($password),'role'=>'superadmin','is_active'=>true,'email_verified_at'=>now()]);
        $roles->assignDefault($user);
        $this->info('SuperAdmin hasaby döredildi: '.$email);
        return self::SUCCESS;
    }
}
