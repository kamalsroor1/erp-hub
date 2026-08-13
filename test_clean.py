import subprocess

code = """
DB::table('users')->whereIn('phone', ['01055554444','01033332222'])->delete();
DB::table('stores')->whereIn('code', ['SHOP-MAADI','VAN-E2E-02','TEMP-SHOP-99'])->delete();
DB::table('roles')->where('name', 'branch_manager')->delete();
"""
res = subprocess.run(["php", "artisan", "tinker", f"--execute={code}"], capture_output=True, text=True)
print("OUT:", res.stdout)
print("ERR:", res.stderr)
