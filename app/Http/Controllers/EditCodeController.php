<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\EditCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Port of ajax-get-codes-contents.php (menu "Manage Error Codes"). */
class EditCodeController extends Controller
{
    public function index(): View
    {
        return view('codes.index', [
            'codes' => EditCode::active()->orderByDesc('Code_ID')->paginate(config('binvoice.per_page.codes')),
        ]);
    }

    public function create(): View
    {
        return view('codes.form', ['code' => new EditCode(['Code_Type' => 'Designer'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        EditCode::create($data + ['Effective_Date' => now(), 'Session_ID' => ActionLog::record('CodeInsert')]);

        return redirect()->route('codes.index')->with('success', 'Code has been successfully added.');
    }

    public function edit(EditCode $code): View
    {
        return view('codes.form', compact('code'));
    }

    public function update(Request $request, EditCode $code): RedirectResponse
    {
        $code->update($this->validated($request) + ['Session_ID' => ActionLog::record('CodeInsert')]);

        return redirect()->route('codes.index')->with('success', 'Code has been successfully updated.');
    }

    public function destroy(EditCode $code): RedirectResponse
    {
        $code->forceFill(['End_Date' => now()])->save();

        return redirect()->route('codes.index')->with('success', 'Code has been successfully deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'Narrative' => ['required', 'string', 'max:50'],
            'Code_Type' => ['required', Rule::in(config('binvoice.edit_code_types'))],
        ], [], ['Narrative' => 'code name', 'Code_Type' => 'code type']);
    }
}
