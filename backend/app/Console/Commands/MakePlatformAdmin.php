<?php
namespace App\Console\Commands;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
class MakePlatformAdmin extends Command
{
 protected $signature='platform:make-admin {email : Administrator email} {--name= : Full name}';protected $description='Create or promote a platform super administrator.';
 public function handle():int{$email=strtolower(trim($this->argument('email')));$name=$this->option('name')?:$this->ask('Full name');$password=$this->secret('Password (minimum 12 characters)');if(strlen((string)$password)<12){$this->error('Password must contain at least 12 characters.');return self::FAILURE;}$user=User::updateOrCreate(['email'=>$email],['name'=>$name,'password'=>Hash::make($password),'is_super_admin'=>true]);$this->info("Platform administrator ready: {$user->email}");return self::SUCCESS;}
}
