#!/bin/bash
# Simple script to restore AppController.php system() method to original version

cd ~/public_html

# Backup the current file first
cp app/Http/Controllers/API/AppController.php app/Http/Controllers/API/AppController.php.backup

# Use sed to replace the system() method with the original simple version
# This replaces from "public function system" to the closing brace of that method
sed -i '/public function system(Request $request)/,/^    }$/c\
    public function system(Request $request)\
    {\
        $explode_url = explode(\x27,\x27, env(\x27ADEX_APP_KEY\x27));\
        if (in_array($request->headers->get(\x27origin\x27), $explode_url)) {\
            try {\
                return response()->json([\
                    \x27status\x27 => \x27success\x27,\
                    \x27setting\x27 => $this->core(),\
                    \x27feature\x27 => $this->feature(),\
                    \x27general\x27 => $this->general(),\
                    \x27bank\x27 => DB::table(\x27adex_key\x27)->select(\x27account_number\x27, \x27account_name\x27, \x27bank_name\x27, \x27min\x27, \x27max\x27)->first()\
                ]);\
            } catch (\\Exception $e) {\
                \\Log::error(\x27System endpoint error: \x27 . $e->getMessage(), [\
                    \x27trace\x27 => $e->getTraceAsString()\
                ]);\
                return response()->json([\
                    \x27status\x27 => false,\
                    \x27message\x27 => \x27An error occurred. Please try again later.\x27\
                ])->setStatusCode(500);\
            }\
        } else {\
            return response()->json([\
                \x27status\x27 => 403,\
                \x27message\x27 => \x27Unable to Authenticate System\x27\
            ])->setStatusCode(403);\
        }\
    }' app/Http/Controllers/API/AppController.php

echo "✓ AppController.php restored!"
echo "Now run: php artisan config:clear && php artisan cache:clear"

