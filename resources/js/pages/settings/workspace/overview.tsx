import { Form, Head } from '@inertiajs/react';

import WorkspaceSettingsController from '@/actions/App/Http/Controllers/Settings/WorkspaceSettingsController';
import WorkspaceController from '@/actions/App/Http/Controllers/WorkspaceController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Props = {
    workspace: {
        id: string;
        name: string;
        slug: string;
        logo: string;
        owner_id: string;
    };
    canManage: boolean;
    isOwner: boolean;
};

export default function WorkspaceOverview({
    workspace,
    canManage,
    isOwner,
}: Props) {
    return (
        <>
            <Head title="Workspace settings" />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Workspace information"
                    description="Update your workspace name and settings"
                />

                <Form
                    {...WorkspaceSettingsController.update.form()}
                    options={{ preserveScroll: true }}
                    className="space-y-6"
                >
                    {({ processing, errors, recentlySuccessful }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Workspace name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    autoComplete="organization"
                                    defaultValue={workspace.name}
                                    disabled={!canManage || processing}
                                    placeholder="Workspace name"
                                    required
                                />
                                <InputError message={errors.name} />
                            </div>

                            {canManage && (
                                <div className="flex items-center gap-4">
                                    <Button type="submit" disabled={processing}>
                                        {processing ? 'Saving...' : 'Save'}
                                    </Button>
                                    {recentlySuccessful && (
                                        <p className="text-sm text-muted-foreground">
                                            Saved
                                        </p>
                                    )}
                                </div>
                            )}
                        </>
                    )}
                </Form>

                {!isOwner && (
                    <div className="space-y-4">
                        <Heading
                            variant="small"
                            title="Leave workspace"
                            description="Remove yourself from this workspace."
                        />
                        <div className="flex items-center justify-between rounded-md border p-4">
                            <div>
                                <p className="font-medium">Leave workspace</p>
                                <p className="text-sm text-muted-foreground">
                                    You'll lose access until you're invited
                                    again.
                                </p>
                            </div>
                            <Form
                                {...WorkspaceController.leave.form(
                                    workspace.id,
                                )}
                                options={{ preserveScroll: true }}
                                onBefore={() =>
                                    confirm('Leave this workspace?')
                                }
                            >
                                {({ processing }) => (
                                    <Button
                                        type="submit"
                                        variant="outline"
                                        disabled={processing}
                                    >
                                        Leave workspace
                                    </Button>
                                )}
                            </Form>
                        </div>
                    </div>
                )}

                {isOwner && (
                    <div className="space-y-4">
                        <Heading
                            variant="small"
                            title="Danger zone"
                            description="Deleting a workspace is permanent and removes all members and data."
                        />
                        <div className="flex items-center justify-between rounded-md border border-destructive/30 p-4">
                            <div>
                                <p className="font-medium">Delete workspace</p>
                                <p className="text-sm text-muted-foreground">
                                    This action cannot be undone.
                                </p>
                            </div>
                            <Form
                                {...WorkspaceController.destroy.form(
                                    workspace.id,
                                )}
                                options={{ preserveScroll: true }}
                                onBefore={() =>
                                    confirm(
                                        'Delete this workspace permanently? This cannot be undone.',
                                    )
                                }
                            >
                                {({ processing }) => (
                                    <Button
                                        type="submit"
                                        variant="destructive"
                                        disabled={processing}
                                    >
                                        Delete workspace
                                    </Button>
                                )}
                            </Form>
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

WorkspaceOverview.layout = {
    breadcrumbs: [
        {
            title: 'Workspace settings',
            href: WorkspaceSettingsController.showOverview().url,
        },
    ],
};
